graph LR;
  generateFunctionCallGraph
  generateFunctionCallGraph-->generateFunctionCallGraph
  generateFunctionCallGraph-->file_get_contents
  generateFunctionCallGraph-->token_get_all
  generateFunctionCallGraph-->is_array
  generateFunctionCallGraph-->T_FUNCTION
  generateFunctionCallGraph-->is_array
  generateFunctionCallGraph-->T_STRING
  generateFunctionCallGraph-->is_array
  generateFunctionCallGraph-->T_STRING
  generateFunctionCallGraph-->formatFunctionName
  generateFunctionCallGraph-->formatFunctionName
  generateFunctionCallGraph-->formatFunctionName
  formatFunctionName
  formatFunctionName-->formatFunctionName
  formatFunctionName-->str_replace
