-- Create function to automatically set user_id for appointments
create or replace function public.set_appointment_user()
returns trigger
language plpgsql
security definer
as $$
begin
  if new.user_id is null then
    new.user_id := auth.uid();
  end if;
  return new;
end;
$$;

-- Drop existing trigger if it exists
drop trigger if exists trg_set_appointment_user on public.appointments;

-- Create trigger to automatically set user_id before insert
create trigger trg_set_appointment_user
before insert on public.appointments
for each row execute function public.set_appointment_user();