import { useQuery, useQueryClient } from "@tanstack/react-query";
import { api } from "@/lib/db";

/** کلیدهای React Query به صورت متمرکز */
export const qk = {
  branches: ["branches"] as const,
  categories: ["categories"] as const,
  products: ["products"] as const,
  orders: ["orders"] as const,
  coupons: ["coupons"] as const,
  settings: ["settings"] as const,
  reviews: ["reviews"] as const,
};

export function useBranches() {
  return useQuery({ queryKey: qk.branches, queryFn: () => api.getBranches() });
}

export function useCategories() {
  return useQuery({ queryKey: qk.categories, queryFn: () => api.getCategories() });
}

export function useProducts() {
  return useQuery({ queryKey: qk.products, queryFn: () => api.getProducts() });
}

export function useSettings() {
  return useQuery({ queryKey: qk.settings, queryFn: () => api.getSettings() });
}

export function useReviews() {
  return useQuery({ queryKey: qk.reviews, queryFn: () => api.getReviews() });
}

export function useCoupons() {
  return useQuery({ queryKey: qk.coupons, queryFn: () => api.getCoupons() });
}

/** سفارش‌ها با به‌روزرسانی خودکار (برای پیشرفت وضعیت) */
export function useOrders(refresh = false) {
  return useQuery({
    queryKey: qk.orders,
    queryFn: () => api.getOrders(),
    refetchInterval: refresh ? 8000 : false,
  });
}

export function useOrdersByPhone(phone: string | undefined, refresh = true) {
  return useQuery({
    queryKey: ["orders", "phone", phone],
    queryFn: () => api.getOrdersByPhone(phone!),
    enabled: Boolean(phone),
    refetchInterval: refresh ? 10000 : false,
  });
}

export function useOrderByCode(code: string | undefined, refresh = true) {
  return useQuery({
    queryKey: ["orders", "code", code],
    queryFn: () => api.getOrderByCode(code!),
    enabled: Boolean(code),
    refetchInterval: refresh ? 8000 : false,
  });
}

/** باطل کردن کش پس از تغییرات مدیریتی */
export function useInvalidate() {
  const qc = useQueryClient();
  return (...keys: readonly unknown[][]) => {
    if (keys.length === 0) return qc.invalidateQueries();
    return Promise.all(keys.map((k) => qc.invalidateQueries({ queryKey: k })));
  };
}
