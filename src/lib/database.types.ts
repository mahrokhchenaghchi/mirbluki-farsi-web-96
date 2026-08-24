export type Json = string | number | boolean | null | { [key: string]: Json | undefined } | Json[];

export interface Database {
  public: {
    Tables: {
      joma_profiles: {
        Row: { id: string; display_name: string | null; created_at: string };
        Insert: { id: string; display_name?: string | null; created_at?: string };
        Update: { id?: string; display_name?: string | null; created_at?: string };
        Relationships: [];
      };
      joma_activities: {
        Row: {
          id: string;
          code: string;
          title: string;
          description: string | null;
          title_specified: boolean;
          created_at: string;
        };
        Insert: {
          id?: string;
          code: string;
          title: string;
          description?: string | null;
          title_specified?: boolean;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_activities"]["Insert"]>;
        Relationships: [];
      };
      joma_periods: {
        Row: {
          id: string;
          user_id: string;
          period_key: string;
          year: number;
          month: number;
          start_date: string;
          end_date: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id?: string;
          period_key: string;
          year: number;
          month: number;
          start_date: string;
          end_date: string;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_periods"]["Insert"]>;
        Relationships: [];
      };
      joma_plans: {
        Row: {
          id: string;
          user_id: string;
          period_id: string;
          period_key: string;
          status: "DRAFT" | "PLANNING" | "RUNNING" | "ARCHIVED";
          created_at: string;
          updated_at: string;
          finalized_at: string | null;
          started_at: string | null;
          archived_at: string | null;
        };
        Insert: {
          id?: string;
          user_id?: string;
          period_id: string;
          period_key: string;
          status?: "DRAFT" | "PLANNING" | "RUNNING" | "ARCHIVED";
          created_at?: string;
          updated_at?: string;
          finalized_at?: string | null;
          started_at?: string | null;
          archived_at?: string | null;
        };
        Update: Partial<Database["public"]["Tables"]["joma_plans"]["Insert"]>;
        Relationships: [];
      };
      joma_plan_activities: {
        Row: {
          id: string;
          user_id: string;
          plan_id: string;
          period_key: string;
          activity_id: string;
          activity_code: string;
          title: string;
          description: string | null;
          frequency: "DAILY" | "WEEKLY" | "MONTHLY";
          target_value: number;
          weight: number;
          snapshot_at: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id?: string;
          plan_id: string;
          period_key: string;
          activity_id: string;
          activity_code: string;
          title: string;
          description?: string | null;
          frequency: "DAILY" | "WEEKLY" | "MONTHLY";
          target_value: number;
          weight: number;
          snapshot_at?: string;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_plan_activities"]["Insert"]>;
        Relationships: [];
      };
      joma_performance_events: {
        Row: {
          id: string;
          user_id: string;
          plan_id: string;
          plan_activity_id: string;
          period_key: string;
          frequency: "DAILY" | "WEEKLY" | "MONTHLY";
          event_type: string;
          performance_date: string;
          actual_value: number;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id?: string;
          plan_id: string;
          plan_activity_id: string;
          period_key: string;
          frequency: "DAILY" | "WEEKLY" | "MONTHLY";
          event_type?: string;
          performance_date: string;
          actual_value: number;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_performance_events"]["Insert"]>;
        Relationships: [];
      };
      joma_mood_records: {
        Row: {
          id: string;
          user_id: string;
          jalali_date: string;
          metrics: Json;
          metrics_status: string;
          created_at: string;
        };
        Insert: {
          id?: string;
          user_id?: string;
          jalali_date: string;
          metrics?: Json;
          metrics_status?: string;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_mood_records"]["Insert"]>;
        Relationships: [];
      };
      joma_mood_metric_definitions: {
        Row: {
          id: string;
          key: string;
          title: string;
          sort_order: number;
          specified: boolean;
          created_at: string;
        };
        Insert: {
          id?: string;
          key: string;
          title: string;
          sort_order: number;
          specified?: boolean;
          created_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_mood_metric_definitions"]["Insert"]>;
        Relationships: [];
      };
      joma_report_projections: {
        Row: {
          id: string;
          user_id: string;
          plan_id: string;
          period_key: string;
          payload: Json;
          source_event_count: number;
          generated_at: string;
        };
        Insert: {
          id?: string;
          user_id?: string;
          plan_id: string;
          period_key: string;
          payload: Json;
          source_event_count?: number;
          generated_at?: string;
        };
        Update: Partial<Database["public"]["Tables"]["joma_report_projections"]["Insert"]>;
        Relationships: [];
      };
      joma_settings: {
        Row: { user_id: string; payload: Json; updated_at: string };
        Insert: { user_id?: string; payload?: Json; updated_at?: string };
        Update: Partial<Database["public"]["Tables"]["joma_settings"]["Insert"]>;
        Relationships: [];
      };
    };
    Views: Record<string, never>;
    Functions: {
      joma_ensure_period: {
        Args: {
          p_period_key: string;
          p_year: number;
          p_month: number;
          p_start_date: string;
          p_end_date: string;
        };
        Returns: string;
      };
      joma_register_performance: {
        Args: {
          p_plan_activity_id: string;
          p_performance_date: string;
          p_actual_value: number;
        };
        Returns: string;
      };
    };
    Enums: Record<string, never>;
    CompositeTypes: Record<string, never>;
  };
}
