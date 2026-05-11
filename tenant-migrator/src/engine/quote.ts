// SQL literal quoting. Mirrors PostgreSQL's quote_nullable() semantics:
//   NULL          -> the bare text "NULL"
//   string        -> single-quoted with embedded quotes doubled
//   number/bool   -> single-quoted as text (PG implicitly casts on insert)
//   bytea/json/arr-> we cast to text on the SOURCE side via SELECT, so by the
//                    time the value reaches us it's already a string in PG's
//                    canonical text format. We just quote it.

export function quoteLiteral(value: unknown): string {
  if (value === null || value === undefined) return "NULL";
  // pg returns Date for timestamp columns -> ISO string.
  if (value instanceof Date) {
    return `'${value.toISOString().replace("T", " ").replace("Z", "")}'`;
  }
  if (typeof value === "boolean") return value ? "'true'" : "'false'";
  if (typeof value === "number" || typeof value === "bigint") return `'${value}'`;
  // Object / array (jsonb columns return parsed JSON via pg).
  if (typeof value === "object") {
    return `'${JSON.stringify(value).replaceAll("'", "''")}'`;
  }
  return `'${String(value).replaceAll("'", "''")}'`;
}

export function quoteIdent(name: string): string {
  return `"${name.replaceAll(`"`, `""`)}"`;
}
