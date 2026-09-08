/**
 * تایپ‌های دیتابیس Supabase.
 * ساختار جدول‌ها دقیقاً مطابق src/lib/types.ts است.
 * این فایل را می‌توان بعداً با `supabase gen types` بازتولید کرد.
 */

export type Json =
  | string
  | number
  | boolean
  | null
  | { [key: string]: Json | undefined }
  | Json[];

export interface Database {
  public: {
    Tables: {
      branches: {
        Row: {
          id: string;
          slug: string;
          name: string;
          city: string;
          address: string;
          phone: string;
          phone2: string | null;
          open_time: string;
          close_time: string;
          lat: number | null;
          lng: number | null;
          image: string | null;
          delivery_fee: number;
          min_order: number;
          free_delivery_over: number;
          is_active: boolean;
          sort: number;
        };
        Insert: Database["public"]["Tables"]["branches"]["Row"];
        Update: Partial<Database["public"]["Tables"]["branches"]["Row"]>;
      };
      categories: {
        Row: {
          id: string;
          slug: string;
          name: string;
          icon: string | null;
          is_active: boolean;
          sort: number;
        };
        Insert: Database["public"]["Tables"]["categories"]["Row"];
        Update: Partial<Database["public"]["Tables"]["categories"]["Row"]>;
      };
      products: {
        Row: {
          id: string;
          category_id: string;
          name: string;
          description: string;
          image: string;
          base_price: number;
          sizes: Json;
          is_available: boolean;
          is_featured: boolean;
          discount_percent: number;
          spicy: boolean;
          vegetarian: boolean;
          calories: number | null;
          prep_minutes: number | null;
          rating: number;
          rating_count: number;
          sort: number;
        };
        Insert: Database["public"]["Tables"]["products"]["Row"];
        Update: Partial<Database["public"]["Tables"]["products"]["Row"]>;
      };
      orders: {
        Row: {
          id: string;
          code: string;
          branch_id: string;
          branch_name: string;
          type: string;
          status: string;
          customer_name: string;
          customer_phone: string;
          address: string | null;
          area: string | null;
          items: Json;
          subtotal: number;
          delivery_fee: number;
          discount: number;
          total: number;
          coupon_code: string | null;
          payment_method: string;
          note: string | null;
          created_at: string;
          status_history: Json;
          auto_advance: boolean;
        };
        Insert: Database["public"]["Tables"]["orders"]["Row"];
        Update: Partial<Database["public"]["Tables"]["orders"]["Row"]>;
      };
      coupons: {
        Row: {
          id: string;
          code: string;
          type: string;
          value: number;
          min_order: number;
          max_discount: number | null;
          is_active: boolean;
          description: string;
        };
        Insert: Database["public"]["Tables"]["coupons"]["Row"];
        Update: Partial<Database["public"]["Tables"]["coupons"]["Row"]>;
      };
      customers: {
        Row: {
          id: string;
          name: string;
          phone: string;
          addresses: Json;
          created_at: string;
        };
        Insert: Database["public"]["Tables"]["customers"]["Row"];
        Update: Partial<Database["public"]["Tables"]["customers"]["Row"]>;
      };
      reviews: {
        Row: {
          id: string;
          name: string;
          food: string;
          rating: number;
          comment: string;
          date: string;
        };
        Insert: Database["public"]["Tables"]["reviews"]["Row"];
        Update: Partial<Database["public"]["Tables"]["reviews"]["Row"]>;
      };
      settings: {
        Row: {
          id: string;
          data: Json;
        };
        Insert: Database["public"]["Tables"]["settings"]["Row"];
        Update: Partial<Database["public"]["Tables"]["settings"]["Row"]>;
      };
    };
    Views: Record<string, never>;
    Functions: Record<string, never>;
    Enums: Record<string, never>;
  };
}
