import type { JomaUser } from "@/domain/types";
import { validateRegistration } from "@/domain/validation";
import {
  localGetSessionUser,
  localResetPassword,
  localSignIn,
  localSignOut,
  localSignUp,
  localUpdateProfile,
  localUsernameAvailable,
} from "@/persistence/local/db";

export interface RegisterInput {
  firstName: string;
  lastName: string;
  username: string;
  phone: string;
  email: string;
  job: string;
  password: string;
  confirmPassword: string;
}

/** Persistence-agnostic auth service. Local now; Supabase later. */
export const authService = {
  currentUser(): JomaUser | null {
    return localGetSessionUser();
  },
  usernameAvailable(username: string): boolean {
    return localUsernameAvailable(username);
  },
  async signIn(identifier: string, password: string): Promise<JomaUser> {
    return localSignIn(identifier, password);
  },
  async signUp(input: RegisterInput): Promise<JomaUser> {
    const invalid = validateRegistration(input);
    if (invalid) throw new Error(invalid);
    return localSignUp(input);
  },
  async resetPassword(identifier: string, password: string): Promise<void> {
    return localResetPassword(identifier, password);
  },
  async signOut(): Promise<void> {
    localSignOut();
  },
  updateProfile(patch: Parameters<typeof localUpdateProfile>[0]): JomaUser {
    return localUpdateProfile(patch);
  },
};
