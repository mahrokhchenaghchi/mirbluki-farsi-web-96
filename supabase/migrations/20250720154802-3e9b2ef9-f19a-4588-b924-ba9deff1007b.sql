-- Remove overly permissive policies and add secure ones
DROP POLICY IF EXISTS "Anyone can view appointments" ON public.appointments;
DROP POLICY IF EXISTS "Anyone can create appointments" ON public.appointments;

-- Create secure policies that only allow the function to operate
-- Users should not directly access the appointments table
CREATE POLICY "Service role can manage appointments"
ON public.appointments
FOR ALL
TO service_role
USING (true)
WITH CHECK (true);

-- Allow anonymous users to call the submit_appointment function only
-- (Function permissions are handled separately through GRANT EXECUTE)