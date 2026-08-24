-- JOMA core schema
-- Independent of any appointment / clinic tables.

create extension if not exists pgcrypto;

create table if not exists public.joma_profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  display_name text,
  created_at timestamptz not null default now()
);

create table if not exists public.joma_activities (
  id uuid primary key default gen_random_uuid(),
  code text not null unique,
  title text not null,
  description text,
  title_specified boolean not null default false,
  created_at timestamptz not null default now()
);

create table if not exists public.joma_periods (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  period_key text not null,
  year integer not null,
  month integer not null check (month between 1 and 12),
  start_date text not null,
  end_date text not null,
  created_at timestamptz not null default now(),
  unique (user_id, period_key)
);

create table if not exists public.joma_plans (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  period_id uuid not null references public.joma_periods (id) on delete cascade,
  period_key text not null,
  status text not null check (status in ('DRAFT', 'PLANNING', 'RUNNING', 'ARCHIVED')),
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  finalized_at timestamptz,
  started_at timestamptz,
  archived_at timestamptz,
  unique (user_id, period_id)
);

create table if not exists public.joma_plan_activities (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  plan_id uuid not null references public.joma_plans (id) on delete cascade,
  period_key text not null,
  activity_id uuid not null references public.joma_activities (id),
  activity_code text not null,
  title text not null,
  description text,
  frequency text not null check (frequency in ('DAILY', 'WEEKLY', 'MONTHLY')),
  target_value numeric not null check (target_value > 0),
  weight numeric not null check (weight >= 0),
  snapshot_at timestamptz not null default now(),
  created_at timestamptz not null default now(),
  unique (plan_id, activity_id)
);

create table if not exists public.joma_performance_events (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  plan_id uuid not null references public.joma_plans (id) on delete cascade,
  plan_activity_id uuid not null references public.joma_plan_activities (id) on delete cascade,
  period_key text not null,
  frequency text not null check (frequency in ('DAILY', 'WEEKLY', 'MONTHLY')),
  event_type text not null default 'PERFORMANCE_REGISTERED',
  performance_date text not null,
  actual_value numeric not null check (actual_value >= 0),
  created_at timestamptz not null default now()
);

create unique index if not exists joma_daily_performance_unique
  on public.joma_performance_events (plan_activity_id, performance_date)
  where frequency = 'DAILY';

create index if not exists joma_performance_events_user_period_idx
  on public.joma_performance_events (user_id, period_key);

create table if not exists public.joma_mood_records (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  jalali_date text not null,
  metrics jsonb not null default '{}'::jsonb,
  metrics_status text not null default 'UNSPECIFIED',
  created_at timestamptz not null default now(),
  unique (user_id, jalali_date)
);

-- Placeholder architecture for the five mood metrics.
-- Rows stay empty until a product specification defines them.
create table if not exists public.joma_mood_metric_definitions (
  id uuid primary key default gen_random_uuid(),
  key text not null unique,
  title text not null,
  sort_order integer not null,
  specified boolean not null default false,
  created_at timestamptz not null default now()
);

create table if not exists public.joma_report_projections (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users (id) on delete cascade,
  plan_id uuid not null references public.joma_plans (id) on delete cascade,
  period_key text not null,
  payload jsonb not null,
  source_event_count integer not null default 0,
  generated_at timestamptz not null default now(),
  unique (plan_id)
);

create table if not exists public.joma_settings (
  user_id uuid primary key references auth.users (id) on delete cascade,
  payload jsonb not null default '{}'::jsonb,
  updated_at timestamptz not null default now()
);

insert into public.joma_activities (code, title, title_specified)
select
  'ACT' || lpad(gs::text, 3, '0'),
  'ACT' || lpad(gs::text, 3, '0'),
  false
from generate_series(1, 45) as gs
on conflict (code) do nothing;

create or replace function public.joma_set_user_id()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  if new.user_id is null then
    new.user_id := auth.uid();
  end if;
  if auth.uid() is not null and new.user_id is distinct from auth.uid() then
    raise exception 'JOMA: user mismatch';
  end if;
  return new;
end;
$$;

drop trigger if exists trg_joma_periods_user on public.joma_periods;
create trigger trg_joma_periods_user
before insert on public.joma_periods
for each row execute function public.joma_set_user_id();

drop trigger if exists trg_joma_plans_user on public.joma_plans;
create trigger trg_joma_plans_user
before insert on public.joma_plans
for each row execute function public.joma_set_user_id();

drop trigger if exists trg_joma_plan_activities_user on public.joma_plan_activities;
create trigger trg_joma_plan_activities_user
before insert on public.joma_plan_activities
for each row execute function public.joma_set_user_id();

drop trigger if exists trg_joma_events_user on public.joma_performance_events;
create trigger trg_joma_events_user
before insert on public.joma_performance_events
for each row execute function public.joma_set_user_id();

drop trigger if exists trg_joma_mood_user on public.joma_mood_records;
create trigger trg_joma_mood_user
before insert on public.joma_mood_records
for each row execute function public.joma_set_user_id();

create or replace function public.joma_touch_plan_updated_at()
returns trigger
language plpgsql
as $$
begin
  new.updated_at = now();
  return new;
end;
$$;

drop trigger if exists trg_joma_plans_updated on public.joma_plans;
create trigger trg_joma_plans_updated
before update on public.joma_plans
for each row execute function public.joma_touch_plan_updated_at();

create or replace function public.joma_handle_new_user()
returns trigger
language plpgsql
security definer
set search_path = public
as $$
begin
  insert into public.joma_profiles (id, display_name)
  values (
    new.id,
    coalesce(new.raw_user_meta_data->>'display_name', split_part(new.email, '@', 1))
  )
  on conflict (id) do nothing;
  return new;
end;
$$;

drop trigger if exists trg_joma_on_auth_user_created on auth.users;
create trigger trg_joma_on_auth_user_created
after insert on auth.users
for each row execute function public.joma_handle_new_user();

create or replace function public.joma_ensure_period(
  p_period_key text,
  p_year integer,
  p_month integer,
  p_start_date text,
  p_end_date text
)
returns uuid
language plpgsql
security definer
set search_path = public
as $$
declare
  v_user uuid := auth.uid();
  v_period_id uuid;
begin
  if v_user is null then
    raise exception 'JOMA: authentication required';
  end if;

  insert into public.joma_periods (user_id, period_key, year, month, start_date, end_date)
  values (v_user, p_period_key, p_year, p_month, p_start_date, p_end_date)
  on conflict (user_id, period_key) do update
    set start_date = excluded.start_date,
        end_date = excluded.end_date
  returning id into v_period_id;

  if v_period_id is null then
    select id into v_period_id
    from public.joma_periods
    where user_id = v_user and period_key = p_period_key;
  end if;

  insert into public.joma_plans (user_id, period_id, period_key, status)
  values (v_user, v_period_id, p_period_key, 'DRAFT')
  on conflict (user_id, period_id) do nothing;

  return v_period_id;
end;
$$;

create or replace function public.joma_register_performance(
  p_plan_activity_id uuid,
  p_performance_date text,
  p_actual_value numeric
)
returns uuid
language plpgsql
security definer
set search_path = public
as $$
declare
  v_user uuid := auth.uid();
  v_activity public.joma_plan_activities%rowtype;
  v_plan public.joma_plans%rowtype;
  v_period public.joma_periods%rowtype;
  v_event_id uuid;
begin
  if v_user is null then
    raise exception 'JOMA: authentication required';
  end if;

  if p_actual_value is null or p_actual_value < 0 then
    raise exception 'JOMA: invalid actual value';
  end if;

  if p_performance_date is null or p_performance_date !~ '^\d{4}-\d{2}-\d{2}$' then
    raise exception 'JOMA: invalid performance date';
  end if;

  select * into v_activity
  from public.joma_plan_activities
  where id = p_plan_activity_id and user_id = v_user;

  if not found then
    raise exception 'JOMA: plan activity not found';
  end if;

  select * into v_plan
  from public.joma_plans
  where id = v_activity.plan_id and user_id = v_user;

  if v_plan.status <> 'RUNNING' then
    raise exception 'JOMA: plan is not running';
  end if;

  select * into v_period
  from public.joma_periods
  where id = v_plan.period_id and user_id = v_user;

  if p_performance_date < v_period.start_date or p_performance_date > v_period.end_date then
    raise exception 'JOMA: date outside period';
  end if;

  if v_activity.frequency = 'DAILY' then
    if exists (
      select 1
      from public.joma_performance_events
      where plan_activity_id = v_activity.id
        and performance_date = p_performance_date
        and frequency = 'DAILY'
    ) then
      raise exception 'JOMA: daily duplicate';
    end if;
  end if;

  insert into public.joma_performance_events (
    user_id,
    plan_id,
    plan_activity_id,
    period_key,
    frequency,
    event_type,
    performance_date,
    actual_value
  ) values (
    v_user,
    v_plan.id,
    v_activity.id,
    v_plan.period_key,
    v_activity.frequency,
    'PERFORMANCE_REGISTERED',
    p_performance_date,
    p_actual_value
  )
  returning id into v_event_id;

  return v_event_id;
end;
$$;

alter table public.joma_profiles enable row level security;
alter table public.joma_activities enable row level security;
alter table public.joma_periods enable row level security;
alter table public.joma_plans enable row level security;
alter table public.joma_plan_activities enable row level security;
alter table public.joma_performance_events enable row level security;
alter table public.joma_mood_records enable row level security;
alter table public.joma_mood_metric_definitions enable row level security;
alter table public.joma_report_projections enable row level security;
alter table public.joma_settings enable row level security;

drop policy if exists joma_profiles_select on public.joma_profiles;
create policy joma_profiles_select on public.joma_profiles
  for select to authenticated using (id = auth.uid());

drop policy if exists joma_profiles_update on public.joma_profiles;
create policy joma_profiles_update on public.joma_profiles
  for update to authenticated using (id = auth.uid()) with check (id = auth.uid());

drop policy if exists joma_profiles_insert on public.joma_profiles;
create policy joma_profiles_insert on public.joma_profiles
  for insert to authenticated with check (id = auth.uid());

drop policy if exists joma_activities_select on public.joma_activities;
create policy joma_activities_select on public.joma_activities
  for select to authenticated using (true);

drop policy if exists joma_periods_all on public.joma_periods;
create policy joma_periods_all on public.joma_periods
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_plans_all on public.joma_plans;
create policy joma_plans_all on public.joma_plans
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_plan_activities_all on public.joma_plan_activities;
create policy joma_plan_activities_all on public.joma_plan_activities
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_events_all on public.joma_performance_events;
create policy joma_events_all on public.joma_performance_events
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_mood_all on public.joma_mood_records;
create policy joma_mood_all on public.joma_mood_records
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_mood_defs_select on public.joma_mood_metric_definitions;
create policy joma_mood_defs_select on public.joma_mood_metric_definitions
  for select to authenticated using (true);

drop policy if exists joma_projections_all on public.joma_report_projections;
create policy joma_projections_all on public.joma_report_projections
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

drop policy if exists joma_settings_all on public.joma_settings;
create policy joma_settings_all on public.joma_settings
  for all to authenticated using (user_id = auth.uid()) with check (user_id = auth.uid());

grant execute on function public.joma_ensure_period(text, integer, integer, text, text) to authenticated;
grant execute on function public.joma_register_performance(uuid, text, numeric) to authenticated;
