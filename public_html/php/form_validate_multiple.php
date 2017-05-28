<?php
/*
 * A wrapper function to wrap multiple input types like file upload inputs with multiple attribute
 */
require 'php/form_validate.php';

$form_validate_multiple = function ($validationSpecs, $formInputs, $fieldNames)use($form_validate) {
    $formInputsMultiple = [];
    $validationSpecsMultiple = [];
    $formInputIdx;
    $fieldNameIdx = 0;
    $readFirst = false;
    foreach ($formInputs as $formInput => $formInputValue) {
        foreach ($formInputValue as $formInputsName => $formInputsValue) {
            $formInputIdx = 0;

            foreach ($formInputsValue as $value) {
                if(!$readFirst){
                    $formInputsMultiple[$formInput . $formInputIdx] = ['__o_fi'=>$fieldNameIdx];
                    ($validationSpecsMultiple[$formInput . $formInputIdx] = $validationSpecs[$formInput]);
                }
                $formInputsMultiple[$formInput . $formInputIdx][$formInputsName] = $value;
                ++$formInputIdx;
            }

            $readFirst = true;
        }
        ++$fieldNameIdx;
    }
    
    $validationResult = $form_validate($validationSpecsMultiple, $formInputsMultiple);
    if(!empty($validationResult)){
        $idx = $formInputsMultiple[$validationResult['field']]['__o_fi'];
        $validationResult['field'] = $fieldNames[$idx];
        $validationResult['index'] = $idx;
    }
    return $validationResult;
};