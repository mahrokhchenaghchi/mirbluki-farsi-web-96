-- Ensure anonymous users can execute the submit_appointment function
GRANT EXECUTE ON FUNCTION public.submit_appointment(text, text, text, text) TO anon;

-- Also grant to authenticated users for completeness  
GRANT EXECUTE ON FUNCTION public.submit_appointment(text, text, text, text) TO authenticated;