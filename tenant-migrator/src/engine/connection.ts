// Connection manager: loads connection presets from YAML, hands out pg.Pool
// instances. Connections are referenced by name from recipes.

import { readFileSync, existsSync } from "node:fs";
import { resolve } from "node:path";
import { Pool } from "pg";
import YAML from "yaml";
import { z } from "zod";

const ConnectionEntry = z.object({
  host:     z.string(),
  port:     z.number().int().default(5432),
  user:     z.string(),
  password: z.string(),
  database: z.string(),
  // When true, this connection is never accepted as a write target. The UI
  // hides it from the Target dropdown and the IPC layer rejects it as the
  // target of any engine action. Use for production sources that must never
  // be written to.
  readonly: z.boolean().default(false)
});

const ConnectionsFile = z.object({
  connections: z.record(ConnectionEntry)
});

export type ConnectionEntry = z.infer<typeof ConnectionEntry>;

export class ConnectionManager {
  private entries: Record<string, ConnectionEntry>;
  private pools = new Map<string, Pool>();

  private constructor(entries: Record<string, ConnectionEntry>) {
    this.entries = entries;
  }

  static load(filePath?: string): ConnectionManager {
    const path = filePath ?? resolve(process.cwd(), "config/connections.yaml");
    if (!existsSync(path)) {
      throw new Error(
        `connections.yaml not found at ${path}.\n` +
        `Copy config/connections.example.yaml to config/connections.yaml and fill in the values.`
      );
    }
    const parsed = ConnectionsFile.parse(YAML.parse(readFileSync(path, "utf8")));
    return new ConnectionManager(parsed.connections);
  }

  list(): string[] {
    return Object.keys(this.entries);
  }

  get(name: string): ConnectionEntry {
    const entry = this.entries[name];
    if (!entry) {
      throw new Error(
        `Unknown connection "${name}". Known: ${this.list().join(", ")}`
      );
    }
    return entry;
  }

  pool(name: string): Pool {
    const cached = this.pools.get(name);
    if (cached) return cached;
    const cfg = this.get(name);
    const pool = new Pool({
      host:           cfg.host,
      port:           cfg.port,
      user:           cfg.user,
      password:       cfg.password,
      database:       cfg.database,
      max:            5,
      // Keep idle connections short — these are operator tools, not servers.
      idleTimeoutMillis: 30_000
    });
    pool.on("error", err => {
      // Don't crash the process on idle-client errors.
      console.error(`pg pool "${name}" error:`, err.message);
    });
    this.pools.set(name, pool);
    return pool;
  }

  async closeAll(): Promise<void> {
    await Promise.all([...this.pools.values()].map(p => p.end()));
    this.pools.clear();
  }
}
