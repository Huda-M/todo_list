<?php
function validateRequired($value, $field)
{
    return empty($value) ? $field . " is required" : null;
}
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? null : "invalide email";
}
function validatePassword($password)
{
    if (strlen($password) < 6) {
        return "Password must be 6 char";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return "Password must contain uppercase";
    }
    if (!preg_match("/[a-z]/", $password)) {
        return "Password must contain lowercase";
    }
    if (!preg_match("/[0-9]/", $password)) {
        return "Password must contain number";
    }
    return null;
}
function validatepriority($priority)
{
    return filter_var($priority, FILTER_VALIDATE_INT) ? null : "invalide priority";
}
function validateuser($name, $email, $password,$phone)
{
    $fields = [
        'name' => $name,
        'email' => $email,
        'password' =>$password,
        'phone' =>$phone,
    ];
    foreach ($fields as $fieldname => $value) {
        if ($error=validateRequired($value, $fieldname)) {
            return $error;
        }
    }
    if ($error=validateEmail($email)) {
        return $error;
    }
    if ($error = validatePassword($password)) {
        return $error;
    }
    return null;
}
function validatetask($title, $content, $priority,$deadline)
{
    $fields = [
        'title' => $title,
        'content' => $content,
        'priority' =>$priority,
        'deadline' =>$deadline,
    ];
    foreach ($fields as $fieldname => $value) {
        if ($error=validateRequired($value, $fieldname)) {
            return $error;
        }
    }
    if ($error=validatepriority($priority)) {
        return $error;
    }
    return null;
}
