<?php

require_once 'array.php';
require_once 'dec_to_fraction.php';
require_once 'mime_content_type.php';

$_form_validate_minions0 = [
    'nan' => function ($subject, $formInputField) {
        return !$subject || is_nan($subject) ? false : ['message' => $formInputField . ' must not be an integer', 'field' => $formInputField, 'err' => 'nan'];
    }, 'null' => function ($subject, $formInputField) {
        return !$subject || is_null($subject) ? false : ['message' => $formInputField . ' is not a null', 'field' => $formInputField, 'err' => 'null'];
    }, 'long' => function ($subject, $formInputField) {
        return !$subject || is_long(+$subject) ? false : ['message' => $formInputField . ' is not a long', 'field' => $formInputField, 'err' => 'long'];
    }, 'integer' => function ($subject, $formInputField) {
        return !$subject || is_integer(+$subject) ? false : ['message' => $formInputField . ' is not an integer', 'field' => $formInputField, 'err' => 'integer'];
    }, 'int' => function ($subject, $formInputField) {
        return !$subject || is_int(+$subject) ? false : ['message' => $formInputField . ' is not an int', 'field' => $formInputField, 'err' => 'int'];
    }, 'array' => function ($subject, $formInputField) {
        return !$subject || is_array($subject) ? false : ['message' => $formInputField . ' is not an array', 'field' => $formInputField, 'err' => 'array'];
    }, 'float' => function ($subject, $formInputField) {
        return !$subject || is_float(+$subject) ? false : ['message' => $formInputField . ' is not a float', 'field' => $formInputField, 'err' => 'float'];
    }, 'double' => function ($subject, $formInputField) {
        return !$subject || is_double(+$subject) ? false : ['message' => $formInputField . ' is not a double', 'field' => $formInputField, 'err' => 'double'];
    }, 'string' => function ($subject, $formInputField) {
        return !$subject || is_string($subject) ? false : ['message' => $formInputField . ' is not a string', 'field' => $formInputField, 'err' => 'string'];
    }, 'boolean' => function ($subject, $formInputField) {
        return !$subject || is_bool($subject) ? false : ['message' => $formInputField . ' is not a boolean', 'field' => $formInputField, 'err' => 'boolean'];
    }, 'filerequired' => function ($subject, $formInputField) {
        return !empty($subject["tmp_name"]) ? false : ['message' => $formInputField . ' is required', 'field' => $formInputField, 'err' => 'filerequired'];
    }, 'numeric' => function ($subject, $formInputField) {
        return !$subject || is_numeric($subject) ? false : ['message' => $formInputField . ' is not a number', 'field' => $formInputField, 'err' => 'numeric'];
    }, 'number' => function ($subject, $formInputField) {
        return !$subject || is_numeric($subject) ? false : ['message' => $formInputField . ' is not a number', 'field' => $formInputField, 'err' => 'number'];
    }, 'day' => function ($subject, $formInputField) {
        return !$subject || (is_numeric($subject) && ($subject += 0) && $subject < 32) ? false : ['message' => $formInputField . ' is not a valid day', 'field' => $formInputField, 'err' => 'day'];
    }, 'week' => function ($subject, $formInputField) {
        return !$subject || (is_numeric($subject) && ($subject += 0) && $subject < 53) ? false : ['message' => $formInputField . ' is not a valid week', 'field' => $formInputField, 'err' => 'week'];
    }, 'month' => function ($subject, $formInputField) {
        return !$subject || (is_numeric($subject) && ($subject += 0) && $subject < 13) ? false : ['message' => $formInputField . ' is not a valid month', 'field' => $formInputField, 'err' => 'month'];
    }, 'year' => function ($subject, $formInputField) {
        return !$subject || (is_numeric($subject) && ($subject += 0)) ? false : ['message' => $formInputField . ' is not a valid year', 'field' => $formInputField, 'err' => 'year'];
    }, 'dob' => function ($subject, $formInputField) {
        return !$subject || (($dob = explode('/', $subject)) && (is_array($dob) && count($dob) === 3) && ($month = intval($dob[1])) && ($day = intval($dob[0])) && ($year = intval($dob[2])) && checkdate($month, $day, $year)) ? false : ['message' => $formInputField . ' is not a valid date of birth', 'field' => $formInputField, 'err' => 'dob'];
    }, 'email' => function ($subject, $formInputField) {
        return !$subject || filter_var($subject, FILTER_VALIDATE_EMAIL) ? false : ['message' => $formInputField . ' is not a valid email address', 'field' => $formInputField, 'err' => 'email'];
    }, 'url' => function ($subject, $formInputField) {
        return !$subject || filter_var($subject, FILTER_VALIDATE_URL) ? false : ['message' => $formInputField . ' is not a valid email address', 'field' => $formInputField, 'err' => 'email'];
    }, 'phone' => function ($subject, $formInputField) {
        return !$subject || (preg_match('/^[\d\+\-\.\(\)\/\s]*$/', $subject)) ? false : ['message' => $formInputField . ' is not a valid phone number', 'field' => $formInputField, 'err' => 'phone'];
    }, 'required' => function ($subject, $formInputField) {
        return $subject ? false : ['message' => $formInputField . ' is required', 'field' => $formInputField, 'err' => 'required'];
    }, 'digits' => function ($subject, $formInputField) {
        return !$subject || ctype_digit($subject) ? false : ['message' => $formInputField . ' doesn\'t contain only digits', 'field' => $formInputField, 'err' => 'digits'];
    }, 'alphabets' => function ($subject, $formInputField) {
        return !$subject || ctype_alpha($subject) ? false : ['message' => $formInputField . ' doesn\'t contain only alphabets', 'field' => $formInputField, 'err' => 'alphabets'];
    }, 'uppercase' => function ($subject, $formInputField) {
        return !$subject || ctype_upper($subject) ? false : ['message' => $formInputField . ' is not upper cased', 'field' => $formInputField, 'err' => 'uppercase'];
    }, 'lowercase' => function ($subject, $formInputField) {
        return !$subject || ctype_lower($subject) ? false : ['message' => $formInputField . ' is not lower cased', 'field' => $formInputField, 'err' => 'lowercase'];
    }, 'alnum' => function ($subject, $formInputField) {
        return !$subject || ctype_alnum($subject) ? false : ['message' => $formInputField . ' contains neither numbers nor alphabets', 'field' => $formInputField, 'err' => 'alnum'];
    }
];
$_form_validate_minions1 = [
    /*
     * imagedimensions:min_width=100,max_width=100,min_height=100,max_height=100,width=100,height=100,ratio=1/1
     * */
    'in' => function ($subject, $array, $formInputField) {
        $array = explode(',', $array);
        return empty($array) || in_array($subject, $array) ? false : ['message' => $subject . ' ' . $formInputField . ' is not supported. Only ' . array_reduce($array, function($carry, $item) {
                        return $carry . ($carry ? ', ' : '') . $item;
                    }, '') . ' are supported', 'field' => $formInputField, 'err' => 'in'];
    }, 'imagedimensions' => function ($subject, $imagedimensions, $formInputField) {
        if (empty($subject["tmp_name"])) {
            return;
        }

        $imagedimensions = explode(',', $imagedimensions);
        $image = getimagesize($subject["tmp_name"]);
        $width = $image[0];
        $height = $image[1];
        $key;
        $value;

        foreach ($imagedimensions as $imagedimension) {
            $imagedimension = explode('=', $imagedimension);
            $key = $imagedimension[0];
            $value = $imagedimension[1];
            switch ($key) {
                case 'min_width':
                    if ($width < $value) {
                        return ['message' => 'The width of ' . $formInputField . ' is shorter than ' . $value . 'px', 'field' => $formInputField, 'err' => 'min_width'];
                    }
                    break;
                case 'min_height':
                    if ($height < $value) {
                        return ['message' => 'The height of ' . $formInputField . ' is shorter than ' . $value . 'px', 'field' => $formInputField, 'err' => 'min_height'];
                    }
                    break;
                case 'max_width':
                    if ($width > $value) {
                        return ['message' => 'The width of ' . $formInputField . ' is longer than ' . $value . 'px', 'field' => $formInputField, 'err' => 'max_width'];
                    }
                    break;
                case 'max_height':
                    if ($height > $value) {
                        return ['message' => 'The height of ' . $formInputField . ' is longer than ' . $value . 'px', 'field' => $formInputField, 'err' => 'max_height'];
                    }
                    break;
                case 'width':
                    if ($width !== $value) {
                        return ['message' => 'The width of ' . $formInputField . ' is not equal to ' . $value . 'px', 'field' => $formInputField, 'err' => 'width'];
                    }
                    break;
                case 'height':
                    if ($width !== $value) {
                        return ['message' => 'The height of ' . $formInputField . ' is not equal to ' . $value . 'px', 'field' => $formInputField, 'err' => 'height'];
                    }
                    break;
                case 'ratio':
                    if (decToFraction($width / $height) !== $value) {
                        return ['message' => 'The aspect ratio of ' . $formInputField . ' is not ' . $value, 'field' => $formInputField, 'err' => 'ratio'];
                    }
                    break;
                default:continue;
            }
        }
    }, 'filemaxmegabytes' => function ($subject, $fileMaxMegaBytes, $formInputField) {
        return empty($subject["tmp_name"]) || filesize($subject["tmp_name"]) <= $fileMaxMegaBytes * 1048576 ? false : ['message' => $formInputField . ' is bigger than ' . $fileMaxMegaBytes . ' MB', 'field' => $formInputField, 'err' => 'filemaxmegabytes'];
    }, 'filemaxsize' => function ($subject, $fileMaxSize, $formInputField) {
        return empty($subject["tmp_name"]) || filesize($subject["tmp_name"]) <= +$fileMaxSize ? false : ['message' => $formInputField . ' is bigger than ' . $fileMaxSize . ' bytes', 'field' => $formInputField, 'err' => 'filemaxsize'];
    }, 'filemimetypes' => function ($subject, $fileMimetypes, $formInputField) {
        $fileMimetypes = explode(',', $fileMimetypes);
        return empty($subject["tmp_name"]) || in_array(mime_content_type($subject["tmp_name"]), $fileMimetypes) ? false : ['message' => $formInputField . ' is not of a supported filetype. Only ' . array_reduce($fileMimetypes, function($carry, $item) {
                        return $carry . ($carry ? ', ' : '') . $item;
                    }, '') . ' are supported', 'field' => $formInputField, 'err' => 'filemimetypes'];
    }, 'eq' => function ($subject, $value, $formInputField) {
        return !$subject || $subject == $value ? false : ['message' => $formInputField . ' is not equals to ' . $value, 'field' => $formInputField, 'err' => 'eq'];
    }, 'gt' => function ($subject, $value, $formInputField) {
        return !$subject || $subject > $value ? false : ['message' => $formInputField . ' is not greater than ' . $value, 'field' => $formInputField, 'err' => 'gt'];
    }, 'lt' => function ($subject, $value, $formInputField) {
        return !$subject || $subject < $value ? false : ['message' => $formInputField . ' is not less than ' . $value, 'field' => $formInputField, 'err' => 'lt'];
    }, 'gte' => function ($subject, $value, $formInputField) {
        return !$subject || $subject >= $value ? false : ['message' => $formInputField . ' is neither greater than or equals to ' . $value, 'field' => $formInputField, 'err' => 'gte'];
    }, 'lte' => function ($subject, $value, $formInputField) {
        return !$subject || $subject <= $value ? false : ['message' => $formInputField . ' is neither less than or equals to ' . $value, 'field' => $formInputField, 'err' => 'lte'];
    }, 'length' => function ($subject, $length, $formInputField) {
        return !$subject || strlen($subject) === (int) $length ? false : ['message' => $formInputField . ' is not ' . $length . ' characters long', 'field' => $formInputField, 'err' => 'length'];
    }, 'minlength' => function ($subject, $minlength, $formInputField) {
        return !$subject || strlen($subject) >= $minlength ? false : ['message' => $formInputField . ' is shorter than ' . $minlength . ' characters long', 'field' => $formInputField, 'err' => 'minlength'];
    }, 'maxlength' => function ($subject, $maxlength, $formInputField) {
        return !$subject || strlen($subject) <= $maxlength ? false : ['message' => $formInputField . ' is longer than ' . $maxlength . ' characters long', 'field' => $formInputField, 'err' => 'maxlength'];
    }, 'arraylength' => function ($subject, $length, $formInputField) {
        return !$subject || count($subject) === (int) $length ? false : ['message' => $formInputField . ' is not ' . $length . ' items long', 'field' => $formInputField, 'err' => 'arraylength'];
    }, 'arrayminlength' => function ($subject, $minlength, $formInputField) {
        return !$subject || count($subject) >= $minlength ? false : ['message' => $formInputField . ' is shorter than ' . $minlength . ' items long', 'field' => $formInputField, 'err' => 'arrayminlength'];
    }, 'arraymaxlength' => function ($subject, $maxlength, $formInputField) {
        return !$subject || count($subject) <= $maxlength ? false : ['message' => $formInputField . ' is longer than ' . $maxlength . ' items long', 'field' => $formInputField, 'err' => 'arraymaxlength'];
    }];

$_form_validate_minions2 = [
    'equalsToField' => function($subject, $fieldName, $formInputs, $formInputField) {
        return !$subject || !strcmp($subject, isset($formInputs[$fieldName]) ? $formInputs[$fieldName] : '') ? false : ['message' => $formInputField . ' and ' . $fieldName . ' are not the same', 'field' => $formInputField, 'err' => 'equalsToField'];
    }, 'required_with' => function($subject, $fieldNames, $formInputs, $formInputField)use($u_array_walk) {
        //required_with:foo,bar,...
        return empty($subject) || $u_array_walk(explode(',', $fieldNames), function($fieldName)use($formInputs) {
                            return isset($formInputs[$fieldName]);
                        }) ? false : ['message' => $formInputField . ' is required only if any of ' . $fieldNames . ' are present', 'field' => $formInputField, 'err' => 'required_with'];
    }, 'required_with_all' => function($subject, $fieldNames, $formInputs, $formInputField)use($u_array_walk) {
        //required_with_all:foo,bar,...
        return empty($subject) || !$u_array_walk(explode(',', $fieldNames), function($fieldName)use($formInputs) {
                            return !isset($formInputs[$fieldName]);
                        }) ? false : ['message' => $formInputField . ' is required only if all of ' . $fieldNames . ' are present', 'field' => $formInputField, 'err' => 'required_with_all'];
    }, 'required_without' => function($subject, $fieldNames, $formInputs, $formInputField)use($u_array_walk) {
        //required_without:foo,bar,...
        return empty($subject) || $u_array_walk(explode(',', $fieldNames), function($fieldName)use($formInputs) {
                            return !isset($formInputs[$fieldName]);
                        }) ? false : ['message' => $formInputField . ' is required only if any of ' . $fieldNames . ' are not present', 'field' => $formInputField, 'err' => 'required_without'];
    }, 'required_without_all' => function($subject, $fieldNames, $formInputs, $formInputField)use($u_array_walk) {
        //required_without_all:foo,bar,...
        return empty($subject) || !$u_array_walk(explode(',', $fieldNames), function($fieldName)use($formInputs) {
                            return isset($formInputs[$fieldName]);
                        }) ? false : ['message' => $formInputField . ' is required only if all of ' . $fieldNames . ' are not present', 'field' => $formInputField, 'err' => 'required_without_all'];
    }, 'required_if' => function($subject, $params, $formInputs, $formInputField)use($u_array_walk) {
        //required_if:anotherfield,value,...
        $params = explode(',', $params);
        $field = $params[0];
        $values = array_slice($params, 1);
        return empty($subject) || (isset($formInputs[$field]) && $u_array_walk($values, function($fieldValue)use($formInputs, $field) {
                            return $formInputs[$field] === $fieldValue;
                        })) ? false : ['message' => $formInputField . ' is required only if ' . $field . ' is equal to any of these values ' . join(',', $values), 'field' => $formInputField, 'err' => 'required_if'];
    }, 'required_unless' => function($subject, $params, $formInputs, $formInputField)use($u_array_walk) {
        //required_unless:anotherfield,value,...
        $params = explode(',', $params);
        $field = $params[0];
        $values = array_slice($params, 1);
        return empty($subject) || !isset($formInputs[$field]) || !$u_array_walk($values, function($fieldValue)use($formInputs, $field) {
                            return $formInputs[$field] === $fieldValue;
                        }) ? false : ['message' => $formInputField . ' is required only if ' . $field . ' is not equal to any of these values ' . join(',', $values), 'field' => $formInputField, 'err' => 'required_unless'];
    },'same' => function($subject, $fieldName, $formInputs, $formInputField) {
        //same:field
        return !$subject || (isset($formInputs[$fieldName]) ? $formInputs[$fieldName] : '') === $subject ? false : ['message' => $formInputField . ' and ' . $fieldName . ' are not the same', 'field' => $formInputField, 'err' => 'same'];
    },'samei' => function($subject, $fieldName, $formInputs, $formInputField) {
        //samei:field
        return !$subject || !strcasecmp($subject, isset($formInputs[$fieldName]) ? $formInputs[$fieldName] : '') ? false : ['message' => $formInputField . ' and ' . $fieldName . ' are not the same', 'field' => $formInputField, 'err' => 'samei'];
    }];

$form_validate = function ($validationSpecs, $formInputs) use ($u_array_walk, $_form_validate_minions0, $_form_validate_minions1, $_form_validate_minions2) {

    return $u_array_walk($validationSpecs, function($validationSpec, $formInputField) use ($formInputs, $u_array_walk, $_form_validate_minions0, $_form_validate_minions1, $_form_validate_minions2) {

        return $u_array_walk(explode('|', $validationSpec), function($spec, $key) use ($formInputField, $formInputs, $_form_validate_minions0, $_form_validate_minions1, $_form_validate_minions2) {
            $spec = trim($spec);
            $formInput = isset($formInputs[$formInputField]) ? $formInputs[$formInputField] : '';

            if (array_key_exists($spec, $_form_validate_minions0)) {
                return $_form_validate_minions0[$spec]($formInput, $formInputField);
            } else if (($specParts = explode(':', $spec)) && array_key_exists($specParts[0], $_form_validate_minions1)) {
                return $_form_validate_minions1[trim($specParts[0])]($formInput, $specParts[1], $formInputField);
            } else {
                return $_form_validate_minions2[trim($specParts[0])]($formInput, $specParts[1], $formInputs, $formInputField);
            }
        });
    });
};
