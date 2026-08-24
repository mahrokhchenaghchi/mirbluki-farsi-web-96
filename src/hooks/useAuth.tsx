import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import type { Session, User } from "@supabase/supabase-js";
import { getJomaEnv } from "@/lib/env";
import { getSupabase } from "@/lib/supabase";
import { toUserMessage } from "@/lib/errors";

interface AuthContextValue {
  user: User | null;
  session: Session | null;
  loading: boolean;
  configured: boolean;
  signIn: (email: string, password: string) => Promise<{ error: string | null }>;
  signUp: (email: string, password: string) => Promise<{ error: string | null; needsConfirmation: boolean }>;
  signOut: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const configured = getJomaEnv().configured;
  const [user, setUser] = useState<User | null>(null);
  const [session, setSession] = useState<Session | null>(null);
  const [loading, setLoading] = useState(configured);

  useEffect(() => {
    if (!configured) {
      setLoading(false);
      return;
    }

    const supabase = getSupabase();
    const { data } = supabase.auth.onAuthStateChange((_event, nextSession) => {
      setSession(nextSession);
      setUser(nextSession?.user ?? null);
      setLoading(false);
    });

    supabase.auth.getSession().then(({ data: sessionData }) => {
      setSession(sessionData.session);
      setUser(sessionData.session?.user ?? null);
      setLoading(false);
    });

    return () => data.subscription.unsubscribe();
  }, [configured]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      session,
      loading,
      configured,
      async signIn(email, password) {
        try {
          const { error } = await getSupabase().auth.signInWithPassword({ email, password });
          return { error: error ? toUserMessage(error) : null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async signUp(email, password) {
        try {
          const { data, error } = await getSupabase().auth.signUp({
            email,
            password,
            options: { emailRedirectTo: `${window.location.origin}/` },
          });
          if (error) return { error: toUserMessage(error), needsConfirmation: false };
          return { error: null, needsConfirmation: !data.session };
        } catch (error) {
          return { error: toUserMessage(error), needsConfirmation: false };
        }
      },
      async signOut() {
        await getSupabase().auth.signOut();
      },
    }),
    [configured, loading, session, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
