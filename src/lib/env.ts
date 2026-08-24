export interface JomaEnv {
  supabaseUrl: string;
  supabaseAnonKey: string;
  configured: boolean;
}

export function getJomaEnv(): JomaEnv {
  const supabaseUrl = (import.meta.env.VITE_SUPABASE_URL as string | undefined)?.trim() ?? "";
  const supabaseAnonKey = (import.meta.env.VITE_SUPABASE_ANON_KEY as string | undefined)?.trim() ?? "";
  return {
    supabaseUrl,
    supabaseAnonKey,
    configured: Boolean(supabaseUrl && supabaseAnonKey),
  };
}
