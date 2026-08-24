import { getJomaEnv } from "./env";

export type JomaRuntimeMode = "local" | "supabase";

export function getRuntimeMode(): JomaRuntimeMode {
  const explicit = (import.meta.env.VITE_JOMA_MODE as string | undefined)?.trim().toLowerCase();
  if (explicit === "local" || explicit === "test") return "local";
  if (explicit === "supabase") return "supabase";
  return getJomaEnv().configured ? "supabase" : "local";
}

export function isLocalMode(): boolean {
  return getRuntimeMode() === "local";
}

export function isAppReady(): boolean {
  return isLocalMode() || getJomaEnv().configured;
}
