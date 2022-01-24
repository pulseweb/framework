<?php
namespace Pulse\Validation;

class ValidationMessage  
{
    private $data = [
        'required' => 'The {field} is required.',
        'min_length' => 'The {field} min length is {parm:0}.',
        'max_length' => 'The {field} max length is {parm:0}.',
        'min' => 'The {field} min value is {parm:0}.',
        'max' => 'The {field} max value is {parm:0}.',
        'valid_email' => 'The {field} is not a valid email.',
        'valid_int' => 'The {field} is not a valid integer.',
        'valid_time' => 'The {field} is not a valid time.',
        'is_unique' => 'The {field} is already used.',
        'valid_date' => 'The {field} is not a valid date',
        'date_compare' => 'The {field} should be {parm:2} of {parm:3}',
        'valid_url' => 'The {field} is not a valid URL.',
        'DateTime' => 'The {field} is not a valid datetime',
        //main validations
        'not_required' => ''
    ];

    public function get($key)
    {
        return $this->data[$key];
    }
    
    public function ReplaceData(string $str, string $field, array $param)
    {
        $pattern = '/{field}/i';
        $str =  preg_replace($pattern, $field, $str);

        $len = count($param);
        for($i = 0; $i < $len; $i++){
            $pattern = "/{parm:$i}/i";
            $str =  preg_replace($pattern, $param[$i], $str);
        }

        return $str;
    }
}
