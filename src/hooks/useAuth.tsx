import { createContext, useContext, useEffect, useMemo, useState, type ReactNode } from "react";
import type { AccessLevel, UserRole } from "@/domain/permissions";
import { hasPermission, type Permission } from "@/domain/permissions";
import { passwordHints } from "@/domain/validation";
import { isAppReady, isLocalMode } from "@/lib/mode";
import { toUserMessage } from "@/lib/errors";
import { authService, type RegisterInput } from "@/services/auth/authService";

export interface AuthUser {
  id: string;
  email: string;
  username: string;
  firstName: string;
  lastName: string;
  fullName: string;
  phone: string;
  job: string;
  role: UserRole;
  accessLevel: AccessLevel;
  createdAt: string;
  preferences: { compactCards: boolean; notificationsEnabled: boolean };
}

interface AuthContextValue {
  user: AuthUser | null;
  loading: boolean;
  configured: boolean;
  localMode: boolean;
  can: (permission: Permission) => boolean;
  signIn: (identifier: string, password: string) => Promise<{ error: string | null }>;
  signUp: (input: RegisterInput) => Promise<{ error: string | null }>;
  resetPassword: (identifier: string, password: string, confirmPassword: string) => Promise<{ error: string | null }>;
  updateProfile: (patch: Partial<Pick<AuthUser, "firstName" | "lastName" | "phone" | "job" | "preferences">>) => void;
  usernameAvailable: (username: string) => boolean;
  signOut: () => Promise<void>;
}

const AuthContext = createContext<AuthContextValue | undefined>(undefined);

function toAuthUser(user: NonNullable<ReturnType<typeof authService.currentUser>>): AuthUser {
  return {
    id: user.id,
    email: user.email,
    username: user.username,
    firstName: user.firstName,
    lastName: user.lastName,
    fullName: user.fullName,
    phone: user.phone,
    job: user.job,
    role: user.role,
    accessLevel: user.accessLevel,
    createdAt: user.createdAt,
    preferences: user.preferences,
  };
}

export function AuthProvider({ children }: { children: ReactNode }) {
  const configured = isAppReady();
  const localMode = isLocalMode();
  const [user, setUser] = useState<AuthUser | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    const session = authService.currentUser();
    setUser(session ? toAuthUser(session) : null);
    setLoading(false);
  }, []);

  const value = useMemo<AuthContextValue>(
    () => ({
      user,
      loading,
      configured,
      localMode,
      can: (permission) => (user ? hasPermission(user.role, permission) : false),
      usernameAvailable: (username) => authService.usernameAvailable(username),
      async signIn(identifier, password) {
        try {
          setUser(toAuthUser(await authService.signIn(identifier, password)));
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async signUp(input) {
        try {
          setUser(toAuthUser(await authService.signUp(input)));
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      async resetPassword(identifier, password, confirmPassword) {
        const hints = passwordHints(password, confirmPassword);
        if (hints.some((item) => !item.ok)) return { error: hints.find((item) => !item.ok)?.text ?? "رمز نامعتبر است." };
        try {
          await authService.resetPassword(identifier, password);
          return { error: null };
        } catch (error) {
          return { error: toUserMessage(error) };
        }
      },
      updateProfile(patch) {
        setUser(toAuthUser(authService.updateProfile(patch)));
      },
      async signOut() {
        await authService.signOut();
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
