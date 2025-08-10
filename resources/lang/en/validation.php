<?php

return [
    'required' => 'The :attribute field is required.',
    'string' => 'The :attribute must be a string.',
    'max' => [
        'string' => 'The :attribute may not be greater than :max characters.',
        'file' => 'The :attribute may not be greater than :max kilobytes.',
    ],
    'email' => 'The :attribute must be a valid email address.',
    'unique' => 'The :attribute has already been taken.',
    'confirmed' => 'The :attribute confirmation does not match.',
    'regex' => 'The :attribute format is invalid.',
    'exists' => 'The selected :attribute is invalid.',
    'in' => 'The selected :attribute is invalid.',
    'image' => 'The :attribute must be an image.',
    'mimes' => 'The :attribute must be a file of type: :values.',

    'attributes' => [
        'name' => 'name',
        'email' => 'email',
        'password' => 'password',
        'old_password' => 'old password',
        'password_confirmation' => 'password confirmation',
        'type' => 'exercise type',
        'muscle_group' => 'muscle group',
        'secondary_muscles' => 'secondary muscles',
        'description' => 'description',
        'instructions' => 'instructions',
        'precautions' => 'precautions',
        'image' => 'image',
        'animation' => 'animation',
        'token' => 'token',
        'id' => 'ID',
        'hash' => 'hash',
    ],
];