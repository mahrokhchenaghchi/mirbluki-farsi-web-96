import { createContext, useContext, useEffect, useState, ReactNode, useCallback } from "react";
import { isSupabaseConfigured, supabase } from "@/integrations/supabase/client";
import { api } from "@/lib/db";
import { isValidIranMobile, normalizePhone } from "@/lib/format";

/**
 * احراز هویت مبتنی بر شماره موبایل (OTP).
 *
 * حالت دمو (پیش‌فرض): کد تایید ثابت 123456 است و در toast نمایش داده می‌شود.
 * حالت Supabase: از signInWithOtp واقعی استفاده می‌شود.
 *
 * کاربرانی که شماره‌شان با settings.admin_phone برابر باشد نقش «مدیر» دارند.
 */

export interface AppUser {
  phone: string;
  name: string;
  isAdmin: boolean;
}

interface AuthContextType {
  user: AppUser | null;
  loading: boolean;
  isAdmin: boolean;
  /** ارسال کد تایید به شماره موبایل */
  requestOtp: (phone: string) => Promise<{ error?: string; demoCode?: string }>;
  /** بررسی کد وارد شده */
  verifyOtp: (phone: string, code: string, name?: string) => Promise<{ error?: string }>;
  signOut: () => void;
  updateName: (name: string) => void;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

const DEMO_CODE = "123456";
const LS_USER = "royal-pizza-user";

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUser] = useState<AppUser | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // بازیابی نشست ذخیره‌شده
    try {
      const raw = localStorage.getItem(LS_USER);
      if (raw) setUser(JSON.parse(raw));
    } catch {
      /* ignore */
    }
    if (isSupabaseConfigured) {
      supabase.auth
        .getSession()
        .then(({ data }) => {
          if (data.session?.user?.phone) {
            setUser({
              phone: data.session.user.phone,
              name: (data.session.user.user_metadata?.name as string) || "مشتری",
              isAdmin: false,
            });
          }
        })
        .catch(() => undefined)
        .finally(() => setLoading(false));
    } else {
      setLoading(false);
    }
  }, []);

  // به‌روزرسانی نقش مدیر وقتی تنظیمات عوض می‌شود
  useEffect(() => {
    if (!user) return;
    let alive = true;
    api.getSettings().then((s) => {
      if (!alive) return;
      if (user.isAdmin !== (user.phone === s.admin_phone)) {
        const updated = { ...user, isAdmin: user.phone === s.admin_phone };
        setUser(updated);
        localStorage.setItem(LS_USER, JSON.stringify(updated));
      }
    });
    return () => {
      alive = false;
    };
  }, [user?.phone]);

  const requestOtp = useCallback(async (phone: string) => {
    const p = normalizePhone(phone);
    if (!isValidIranMobile(p)) {
      return { error: "شماره موبایل معتبر نیست (مثال: ۰۹۱۲۳۴۵۶۷۸۹)" };
    }
    if (isSupabaseConfigured) {
      try {
        const { error } = await supabase.auth.signInWithOtp({ phone: p });
        if (error) return { error: error.message };
        return {};
      } catch {
        return { error: "خطا در ارسال کد. دوباره تلاش کنید." };
      }
    }
    // حالت دمو: کد ثابت برمی‌گردد تا فرآیند قابل تست باشد
    return { demoCode: DEMO_CODE };
  }, []);

  const verifyOtp = useCallback(async (phone: string, code: string, name?: string) => {
    const p = normalizePhone(phone);
    if (!/^\d{6}$/.test(code.trim())) {
      return { error: "کد تایید باید ۶ رقم باشد." };
    }
    if (isSupabaseConfigured) {
      try {
        const { error } = await supabase.auth.verifyOtp({ phone: p, token: code, type: "sms" });
        if (error) return { error: "کد تایید اشتباه است." };
      } catch {
        return { error: "خطا در بررسی کد." };
      }
    } else if (code.trim() !== DEMO_CODE) {
      return { error: "کد تایید اشتباه است." };
    }

    const settings = await api.getSettings();
    const appUser: AppUser = {
      phone: p,
      name: name?.trim() || "مشتری",
      isAdmin: p === settings.admin_phone,
    };
    setUser(appUser);
    localStorage.setItem(LS_USER, JSON.stringify(appUser));
    return {};
  }, []);

  const signOut = useCallback(() => {
    setUser(null);
    localStorage.removeItem(LS_USER);
    if (isSupabaseConfigured) {
      supabase.auth.signOut().catch(() => undefined);
    }
  }, []);

  const updateName = useCallback(
    (name: string) => {
      if (!user) return;
      const updated = { ...user, name };
      setUser(updated);
      localStorage.setItem(LS_USER, JSON.stringify(updated));
    },
    [user]
  );

  return (
    <AuthContext.Provider
      value={{ user, loading, isAdmin: Boolean(user?.isAdmin), requestOtp, verifyOtp, signOut, updateName }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
}
