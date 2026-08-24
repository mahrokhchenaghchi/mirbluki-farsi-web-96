import { createClient, type SupabaseClient } from "@supabase/supabase-js";
import { getJomaEnv } from "./env";
import type { Database } from "./database.types";

let client: SupabaseClient<Database> | null = null;

export function getSupabase(): SupabaseClient<Database> {
  if (client) return client;
  const env = getJomaEnv();
  if (!env.configured) {
    throw new Error("Supabase environment variables are missing.");
  }
  client = createClient<Database>(env.supabaseUrl, env.supabaseAnonKey, {
    auth: {
      persistSession: true,
      autoRefreshToken: true,
      storage: typeof window !== "undefined" ? window.localStorage : undefined,
      detectSessionInUrl: true,
    },
  });
  return client;
}

export function resetSupabaseClient() {
  client = null;
}
