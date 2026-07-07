<?php

return [

    'required' => 'שדה :attribute הינו שדה חובה.',
    'email' => 'יש להזין כתובת דוא"ל תקינה.',
    'unique' => 'כתובת ה:attribute כבר רשומה במערכת.',
    'confirmed' => 'אימות ה:attribute אינו תואם.',
    'current_password' => 'הסיסמה שגויה.',
    'size' => [
        'string' => 'שדה :attribute חייב להכיל :size תווים.',
    ],
    'min' => [
        'string' => 'שדה :attribute חייב להכיל לפחות :min תווים.',
    ],
    'password' => [
        'uncompromised' => ':attribute זו נחשפה בעבר בדליפת מידע. יש לבחור סיסמה אחרת.',
    ],

    'attributes' => [
        'name' => 'שם',
        'email' => 'דוא"ל',
        'password' => 'סיסמה',
        'password_confirmation' => 'אימות סיסמה',
        'code' => 'קוד',
    ],

];
