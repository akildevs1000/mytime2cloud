// Restore: runs the generated SQL on the target inside one transaction.
// Streams progress events so callers (CLI, Electron UI) can show what's happening.

import { Pool } from "pg";

export interface RestoreEvent {
  kind: "begin" | "statement" | "commit" | "rollback" | "error";
  ordinal?: number;       // 1-based statement number
  total?: number;
  affected?: number;      // rows affected by the last statement
  message?: string;
}

export interface RestoreResult {
  inserted: number;
  skipped:  number;       // INSERT ... ON CONFLICT DO NOTHING that landed nothing
  errors:   number;
  durationMs: number;
}

// Naive SQL splitter that respects single-quoted strings, dollar-quoted
// blocks, and `--` line comments. Statements end at `;` outside of these.
export function splitStatements(sql: string): string[] {
  const out: string[] = [];
  let buf = "";
  let i = 0;
  const len = sql.length;

  while (i < len) {
    const c = sql[i];

    // Line comment
    if (c === "-" && sql[i + 1] === "-") {
      while (i < len && sql[i] !== "\n") { buf += sql[i++]; }
      continue;
    }
    // Block comment
    if (c === "/" && sql[i + 1] === "*") {
      buf += "/*"; i += 2;
      while (i < len && !(sql[i] === "*" && sql[i + 1] === "/")) { buf += sql[i++]; }
      if (i < len) { buf += "*/"; i += 2; }
      continue;
    }
    // Dollar-quoted block ($tag$ ... $tag$)
    if (c === "$") {
      const m = sql.slice(i).match(/^\$[A-Za-z0-9_]*\$/);
      if (m) {
        const tag = m[0];
        buf += tag; i += tag.length;
        const end = sql.indexOf(tag, i);
        if (end === -1) {
          // Unterminated — append rest and bail.
          buf += sql.slice(i); i = len;
        } else {
          buf += sql.slice(i, end + tag.length);
          i = end + tag.length;
        }
        continue;
      }
    }
    // Single-quoted string
    if (c === "'") {
      buf += "'"; i++;
      while (i < len) {
        if (sql[i] === "'" && sql[i + 1] === "'") { buf += "''"; i += 2; continue; }
        if (sql[i] === "'") { buf += "'"; i++; break; }
        buf += sql[i++];
      }
      continue;
    }
    if (c === ";") {
      buf += ";";
      const stmt = buf.trim();
      if (stmt && stmt !== ";") out.push(stmt);
      buf = "";
      i++;
      continue;
    }
    buf += c;
    i++;
  }
  const last = buf.trim();
  if (last) out.push(last);
  return out;
}

export async function runRestore(
  targetPool: Pool,
  sql: string,
  onEvent?: (e: RestoreEvent) => void
): Promise<RestoreResult> {
  const stmts = splitStatements(sql);
  const total = stmts.length;
  const start = Date.now();

  let inserted = 0;
  let skipped = 0;
  let errors = 0;

  const client = await targetPool.connect();
  try {
    onEvent?.({ kind: "begin", total });

    let inOurTransaction = false;
    for (let i = 0; i < stmts.length; i++) {
      const s = stmts[i];
      const trimmed = s.trim();
      const upper = trimmed.toUpperCase();

      if (upper === "BEGIN;" || upper === "BEGIN") {
        await client.query("BEGIN");
        inOurTransaction = true;
        onEvent?.({ kind: "statement", ordinal: i + 1, total, message: "BEGIN" });
        continue;
      }
      if (upper === "COMMIT;" || upper === "COMMIT") {
        await client.query("COMMIT");
        inOurTransaction = false;
        onEvent?.({ kind: "commit", ordinal: i + 1, total });
        continue;
      }

      try {
        const res = await client.query(s);
        if (s.startsWith("INSERT")) {
          if ((res.rowCount ?? 0) > 0) inserted++; else skipped++;
        }
        onEvent?.({ kind: "statement", ordinal: i + 1, total, affected: res.rowCount ?? 0 });
      } catch (err) {
        errors++;
        const msg = err instanceof Error ? err.message : String(err);
        onEvent?.({ kind: "error", ordinal: i + 1, total, message: msg });
        if (inOurTransaction) {
          try { await client.query("ROLLBACK"); } catch { /* ignore */ }
          onEvent?.({ kind: "rollback", message: "transaction rolled back" });
        }
        throw err;
      }
    }
  } finally {
    client.release();
  }

  return { inserted, skipped, errors, durationMs: Date.now() - start };
}
