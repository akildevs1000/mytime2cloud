#!/usr/bin/env node
// Tenant-migrator CLI. Wraps the engine in subcommands.

import { Command } from "commander";
import { writeFileSync, readFileSync, existsSync } from "node:fs";
import { resolve } from "node:path";
import chalk from "chalk";
import {
  ConnectionManager,
  loadRecipe,
  loadSchema,
  buildDump,
  runRestore,
  runVerify,
  runAudit
} from "../engine/index.js";

// Logging helpers (declared before commands so they can be referenced anywhere).
const log = {
  section:    (msg: string) => console.log("\n" + chalk.bold.cyan("== " + msg + " ==")),
  subsection: (msg: string) => console.log(chalk.bold("\n-- " + msg + " --")),
  info:       (msg: string) => console.log(chalk.dim("[info] " + msg)),
  warn:       (msg: string) => console.log(chalk.yellow("[warn] " + msg)),
  error:      (msg: string) => console.error(chalk.red("[error] " + msg))
};

async function prompt(q: string): Promise<string> {
  return new Promise(resolve => {
    process.stdout.write(q);
    process.stdin.resume();
    process.stdin.setEncoding("utf8");
    process.stdin.once("data", d => {
      process.stdin.pause();
      resolve(String(d).trim());
    });
  });
}

const program = new Command()
  .name("tenant-migrator")
  .description("Multi-tenant Postgres migration / sync tool for mytime2cloud")
  .version("0.1.0");

// ---------------------------------------------------------------------------
// audit
// ---------------------------------------------------------------------------
program.command("audit")
  .description("Inspect a target DB: tenants, orphan rows, schema diffs")
  .requiredOption("-r, --recipe <path>", "Recipe YAML")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .action(async (opts) => {
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const src = conns.pool(recipe.source);
    const tgt = conns.pool(recipe.target);

    log.section(`audit: target=${recipe.target}, source=${recipe.source}`);
    const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
    const result = await runAudit(tgt, srcSchema, tgtSchema);

    log.subsection("Real tenants on target");
    for (const t of result.tenants) console.log(`  ${t.id}\t${t.name}`);

    log.subsection("Orphan rows (company_id values with no companies row)");
    if (result.orphans.length === 0) console.log("  (none)");
    else for (const o of result.orphans) {
      console.log(`  company_id=${o.company_id}: ${o.rows} row(s) (sample table: ${o.sample_table})`);
    }

    log.subsection("Schema diff");
    if (result.schemaDiff.onlyInSource.length === 0 && result.schemaDiff.onlyInTarget.length === 0) {
      console.log("  (identical)");
    } else {
      if (result.schemaDiff.onlyInSource.length > 0) {
        console.log(`  Only in source: ${result.schemaDiff.onlyInSource.join(", ")}`);
      }
      if (result.schemaDiff.onlyInTarget.length > 0) {
        console.log(`  Only in target: ${result.schemaDiff.onlyInTarget.join(", ")}`);
      }
    }
    await conns.closeAll();
  });

// ---------------------------------------------------------------------------
// dump
// ---------------------------------------------------------------------------
program.command("dump")
  .description("Generate the restore SQL file (does not run it)")
  .requiredOption("-r, --recipe <path>", "Recipe YAML")
  .option("-o, --out <path>", "Output SQL file", "dump.sql")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .action(async (opts) => {
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const src = conns.pool(recipe.source);
    const tgt = conns.pool(recipe.target);

    log.section(`dump: ${recipe.name}`);
    const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
    const result = await buildDump({ recipe, sourcePool: src, targetPool: tgt, sourceSchema: srcSchema, targetSchema: tgtSchema });

    writeFileSync(opts.out, result.sql, "utf8");
    log.subsection("Per-table summary");
    for (const s of result.tableStats) {
      const remapNote = s.remapped ? ` [REMAP ${s.remapped}]` : "";
      const status = s.status === "ok" ? "" : ` (${s.status})`;
      console.log(`  ${s.table.padEnd(38)} ${String(s.rows).padStart(7)} rows${remapNote}${status}`);
    }
    log.subsection("Summary");
    const total = result.tableStats.reduce((a, b) => a + b.rows, 0);
    const remapTotal = [...result.remap.values()].reduce((a, b) => a + b.length, 0);
    console.log(`  Tables: ${result.tableStats.length}`);
    console.log(`  Rows:   ${total}`);
    console.log(`  Remapped IDs: ${remapTotal} across ${result.remap.size} table(s)`);
    console.log(`  Wrote:  ${resolve(opts.out)}`);
    await conns.closeAll();
  });

// ---------------------------------------------------------------------------
// restore
// ---------------------------------------------------------------------------
program.command("restore")
  .description("Run an existing dump SQL file on the target")
  .requiredOption("-r, --recipe <path>", "Recipe YAML (used for target connection)")
  .requiredOption("-f, --file <path>", "Dump SQL file to run")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .action(async (opts) => {
    if (!existsSync(opts.file)) throw new Error(`Dump file not found: ${opts.file}`);
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const tgt = conns.pool(recipe.target);

    log.section(`restore: ${opts.file} -> ${recipe.target}`);
    const sql = readFileSync(opts.file, "utf8");
    const start = Date.now();
    const result = await runRestore(tgt, sql, e => {
      if (e.kind === "error")  console.log(chalk.red(`  ERROR @${e.ordinal}: ${e.message}`));
      if (e.kind === "rollback") console.log(chalk.yellow("  ROLLBACK"));
    });
    log.subsection("Result");
    console.log(`  Inserted: ${chalk.green(result.inserted)}`);
    console.log(`  Skipped : ${result.skipped}`);
    console.log(`  Errors  : ${result.errors}`);
    console.log(`  Duration: ${result.durationMs} ms (overall ${Date.now() - start} ms)`);
    await conns.closeAll();
  });

// ---------------------------------------------------------------------------
// verify
// ---------------------------------------------------------------------------
program.command("verify")
  .description("Compare source vs target row counts table-by-table (read-only)")
  .requiredOption("-r, --recipe <path>", "Recipe YAML")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .action(async (opts) => {
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const src = conns.pool(recipe.source);
    const tgt = conns.pool(recipe.target);

    log.section(`verify: ${recipe.source} vs ${recipe.target}`);
    const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
    const result = await runVerify(recipe, src, tgt, srcSchema, tgtSchema);

    const mismatches = result.rows.filter(r => r.status !== "match");
    if (mismatches.length === 0) {
      console.log(chalk.green(`  ALL ${result.matched} TABLES MATCH`));
    } else {
      log.subsection("Mismatches");
      for (const r of mismatches) {
        const tag = r.status === "missing-on-target" ? chalk.yellow("missing") : chalk.cyan("extra");
        console.log(`  ${r.table.padEnd(36)} src=${String(r.source).padStart(7)} tgt=${String(r.target).padStart(7)}  diff=${r.diff}  (${tag})`);
      }
      log.subsection("Summary");
      console.log(`  matched: ${result.matched}, missing-on-target: ${result.missing}, extra-on-target: ${result.extra}`);
    }
    await conns.closeAll();
  });

// ---------------------------------------------------------------------------
// sync = dump + restore + verify, in one go.
// ---------------------------------------------------------------------------
program.command("sync")
  .description("Generate dump, run on target, verify. (No backup -- run 'backup' separately first.)")
  .requiredOption("-r, --recipe <path>", "Recipe YAML")
  .option("-o, --out <path>", "Output SQL file", "dump.sql")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .option("--yes", "Skip confirmation before running on target")
  .action(async (opts) => {
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const src = conns.pool(recipe.source);
    const tgt = conns.pool(recipe.target);

    log.section(`sync: ${recipe.name}`);
    log.info("Step 1/3: dump");
    const [srcSchema, tgtSchema] = await Promise.all([loadSchema(src), loadSchema(tgt)]);
    const dump = await buildDump({ recipe, sourcePool: src, targetPool: tgt, sourceSchema: srcSchema, targetSchema: tgtSchema });
    writeFileSync(opts.out, dump.sql, "utf8");
    const total = dump.tableStats.reduce((a, b) => a + b.rows, 0);
    const remapTotal = [...dump.remap.values()].reduce((a, b) => a + b.length, 0);
    console.log(`  ${total} INSERT statements written to ${opts.out}, ${remapTotal} remapped id(s)`);

    if (!opts.yes) {
      log.warn(`About to run ${total} INSERTs against ${recipe.target}. Pass --yes to skip this prompt.`);
      const ok = await prompt("Proceed? (y/N) ");
      if (ok.toLowerCase() !== "y") { log.warn("Aborted."); await conns.closeAll(); return; }
    }

    log.info("Step 2/3: restore");
    const r = await runRestore(tgt, dump.sql);
    console.log(`  inserted=${r.inserted}, skipped=${r.skipped}, errors=${r.errors}, ${r.durationMs} ms`);

    log.info("Step 3/3: verify");
    const v = await runVerify(recipe, src, tgt, srcSchema, tgtSchema);
    if (v.missing === 0 && v.extra === 0) {
      console.log(chalk.green(`  ALL ${v.matched} TABLES MATCH`));
    } else {
      console.log(chalk.yellow(`  matched=${v.matched}, missing=${v.missing}, extra=${v.extra}`));
    }
    await conns.closeAll();
  });

// ---------------------------------------------------------------------------
// backup (delegates to pg_dump)
// ---------------------------------------------------------------------------
program.command("backup")
  .description("Take a pg_dump (custom-format) backup of the recipe's target DB")
  .requiredOption("-r, --recipe <path>", "Recipe YAML")
  .option("-o, --out-dir <path>", "Backup directory", "backups")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .option("--pg-dump <path>", "pg_dump executable", "pg_dump")
  .action(async (opts) => {
    const { spawnSync, } = await import("node:child_process");
    const { mkdirSync } = await import("node:fs");
    const recipe = loadRecipe(opts.recipe);
    const conns  = ConnectionManager.load(opts.connections);
    const cfg    = conns.get(recipe.target);

    mkdirSync(opts.outDir, { recursive: true });
    const stamp = new Date().toISOString().replaceAll(":", "").slice(0, 15);
    const file = resolve(opts.outDir, `${recipe.target}_${recipe.name}_${stamp}.dump`);

    log.section(`backup: ${recipe.target} -> ${file}`);
    const env = { ...process.env, PGPASSWORD: cfg.password };
    const proc = spawnSync(opts.pgDump, [
      "-h", cfg.host, "-p", String(cfg.port), "-U", cfg.user, "-d", cfg.database,
      "-F", "c", "-Z", "6", "-f", file
    ], { env, stdio: "inherit" });

    await conns.closeAll();
    if (proc.status !== 0) {
      log.error(`pg_dump exited with status ${proc.status}`);
      process.exit(proc.status ?? 1);
    }
    console.log(chalk.green(`  done: ${file}`));
  });

// ---------------------------------------------------------------------------
// list-connections
// ---------------------------------------------------------------------------
program.command("list-connections")
  .description("List configured DB connection presets")
  .option("-c, --connections <path>", "Connections YAML", "config/connections.yaml")
  .action((opts) => {
    const conns = ConnectionManager.load(opts.connections);
    log.section("Connections");
    for (const name of conns.list()) {
      const cfg = conns.get(name);
      console.log(`  ${name.padEnd(24)} ${cfg.user}@${cfg.host}:${cfg.port}/${cfg.database}`);
    }
  });

// ---------------------------------------------------------------------------
program.parseAsync(process.argv).catch(err => {
  log.error(err instanceof Error ? err.message : String(err));
  process.exit(1);
});
