-- Create function to handle appointment form submissions
CREATE OR REPLACE FUNCTION public.submit_appointment(
  full_name TEXT,
  phone_number TEXT,
  preferred_date TEXT,
  message TEXT DEFAULT NULL
)
RETURNS UUID
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
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

-- Grant execute permission to anonymous users for public form submissions
GRANT EXECUTE ON FUNCTION public.submit_appointment(TEXT, TEXT, TEXT, TEXT) TO anon;