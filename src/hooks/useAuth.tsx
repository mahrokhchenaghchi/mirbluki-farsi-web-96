import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import { validateRegistration } from "@/domain/validation";
import { isAppReady, isLocalMode } from "@/lib/mode";
import { toUserMessage } from "@/lib/errors";
import {
  localGetSessionUser,
  localResetPassword,
  localSignIn,
  localSignOut,
  localSignUp,
} from "@/persistence/local/db";

export interface AuthUser {
  id: string;
  email: string;
  username: string;
  fullName: string;
}

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  configured: boolean;
  localMode: boolean;
  signIn: (identifier: string, password: string) => Promise<{ error: string | null }>;
  signUp: (input: {
    fullName: string;
    username: string;
    phone: string;
    email: string;
    job: string;
    password: string;
    confirmPassword: string;
  }) => Promise<{ error: string | null }>;
  resetPassword: (identifier: string, password: string, confirmPassword: string) => Promise<{ error: string | null }>;
  signOut: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

function toAuthUser(user: { id: string; email: string; username: string; fullName: string }): AuthUser {
  return { id: user.id, email: user.email, username: user.username, fullName: user.fullName };
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const configured = isAppReady();
  const localMode = isLocalMode();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const session = localGetSessionUser();
    setUser(session ? toAuthUser(session) : null);
    setLoading(false);
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      loading,
      configured,
      localMode,
      async signIn(identifier, password) {
        try {
          const localUser = await localSignIn(identifier, password);
          setUser(toAuthUser(localUser));
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async signUp(input) {
        const invalid = validateRegistration(input);
        if (invalid) return { error: invalid };
        try {
          const localUser = await localSignUp(input);
          setUser(toAuthUser(localUser));
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async resetPassword(identifier, password, confirmPassword) {
        if (password.length < 6) return { error: "رمز عبور باید حداقل ۶ نویسه باشد." };
        if (password !== confirmPassword) return { error: "رمز عبور و تکرار آن یکسان نیستند." };
        try {
          await localResetPassword(identifier, password);
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async signOut() {
        localSignOut();
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
