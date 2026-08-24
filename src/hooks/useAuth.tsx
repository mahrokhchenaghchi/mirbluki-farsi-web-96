import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { isAppReady, isLocalMode } from "@/lib/mode";
import { getSupabase } from "@/lib/supabase";
import { toUserMessage } from "@/lib/errors";
import { localGetSession, localSignIn, localSignOut, localSignUp } from "@/persistence/local/db";

export interface AuthUser {
  id: string;
  email: string;
}

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  configured: boolean;
  localMode: boolean;
  signIn: (email: string, password: string) => Promise<{ error: string | null }>;
  signUp: (email: string, password: string) => Promise<{ error: string | null; needsConfirmation: boolean }>;
  signOut: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const configured = isAppReady();
  const localMode = isLocalMode();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!configured) {
      setLoading(false);
      return;
    }

    if (localMode) {
      const session = localGetSession();
      setUser(session ? { id: session.userId, email: session.email } : null);
      setLoading(false);
      return;
    }

    const supabase = getSupabase();
    const { data } = supabase.auth.onAuthStateChange((_event, nextSession) => {
      setUser(nextSession?.user?.email ? { id: nextSession.user.id, email: nextSession.user.email } : null);
      setLoading(false);
    });

    supabase.auth.getSession().then(({ data: sessionData }) => {
      const next = sessionData.session?.user;
      setUser(next?.email ? { id: next.id, email: next.email } : null);
      setLoading(false);
    });

    return () => data.subscription.unsubscribe();
  }, [configured, localMode]);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      loading,
      configured,
      localMode,
      async signIn(email, password) {
        try {
          if (isLocalMode()) {
            const localUser = await localSignIn(email, password);
            setUser({ id: localUser.id, email: localUser.email });
            return { error: null };
          }
          const { error } = await getSupabase().auth.signInWithPassword({ email, password });
          return { error: error ? toUserMessage(error) : null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async signUp(email, password) {
        try {
          if (isLocalMode()) {
            const localUser = await localSignUp(email, password);
            setUser({ id: localUser.id, email: localUser.email });
            return { error: null, needsConfirmation: false };
          }
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
        if (isLocalMode()) {
          localSignOut();
          setUser(null);
          return;
        }
        await getSupabase().auth.signOut();
        setUser(null);
      },
    }),
    [configured, loading, localMode, user],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (!context) throw new Error("useAuth must be used within AuthProvider");
  return context;
}
