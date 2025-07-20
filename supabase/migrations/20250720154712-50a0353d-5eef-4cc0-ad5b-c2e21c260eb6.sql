-- Fix function security issue by updating search_path
CREATE OR REPLACE FUNCTION public.submit_appointment(
  full_name TEXT,
  phone_number TEXT,
  preferred_date TEXT,
  message TEXT DEFAULT NULL
)
RETURNS UUID
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
  appointment_id UUID;
BEGIN
  INSERT INTO public.appointments (
    name,
    phone,
    appointment_type,
    message,
    status
  ) VALUES (
    full_name,
    phone_number,
    'Consultation - ' || preferred_date,
    message,
    'pending'
  )
  RETURNING id INTO appointment_id;
  
  RETURN appointment_id;
END;
$$;

-- Also fix the update trigger function
CREATE OR REPLACE FUNCTION public.update_updated_at_column()
RETURNS TRIGGER 
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
BEGIN
  NEW.updated_at = NOW();
  RETURN NEW;
END;
$$;