<?php

return [
    // UserController messages
    'name_updated' => 'تم تحديث الاسم بنجاح',
    'password_changed' => 'تم تغيير كلمة المرور بنجاح',
    'data_reset' => 'تم إعادة تعيين بيانات المستخدم بنجاح',
    'language_updated' => 'تم تحديث اللغة بنجاح',
    'validation_failed' => 'فشل التحقق من البيانات',
    'invalid_old_password' => 'كلمة المرور القديمة غير صحيحة',
    'user_not_found' => 'المستخدم غير موجود',
    'unauthenticated' => 'غير مصرح',
    'failed_to_update' => 'فشل التحديث: :error',

    // AuthController messages
    'registration_successful' => 'تم التسجيل بنجاح. يرجى التحقق من بريدك الإلكتروني.',
    'login_successful' => 'تم تسجيل الدخول بنجاح',
    'email_not_found' => 'البريد الإلكتروني غير موجود',
    'invalid_password' => 'كلمة المرور غير صحيحة',
    'email_not_verified' => 'البريد الإلكتروني غير مفعّل. يرجى التحقق من بريدك الإلكتروني لرابط التفعيل.',
    'logout_successful' => 'تم تسجيل الخروج بنجاح',
    'password_reset_link_sent' => 'تم إرسال رابط إعادة تعيين كلمة المرور إلى بريدك الإلكتروني',
    'too_many_reset_attempts' => 'محاولات إعادة تعيين كثيرة جدًا. يرجى المحاولة لاحقًا.',
    'unable_to_send_reset_link' => 'غير قادر على إرسال رابط إعادة التعيين',
    'password_reset_successful' => 'تم إعادة تعيين كلمة المرور بنجاح',
    'invalid_token' => 'الرمز غير صالح أو منتهي الصلاحية',
    'email_verification_successful' => 'تم التحقق من البريد الإلكتروني بنجاح',
    'email_already_verified' => 'البريد الإلكتروني مفعّل بالفعل',
    'invalid_verification_link' => 'رابط التحقق غير صالح',
    'invalid_signature' => 'التوقيع غير صالح أو منتهي الصلاحية',
    'verification_email_resent' => 'تم إعادة إرسال بريد التحقق بنجاح. يرجى التحقق من بريدك الإلكتروني.',
    'failed_to_resend_verification' => 'فشل إعادة إرسال بريد التحقق: :error',

    // ExerciseController messages
    'exercises_retrieved' => 'تم استرجاع التمارين بنجاح',
    'exercise_retrieved' => 'تم استرجاع التمرين بنجاح',
    'exercise_created' => 'تم إنشاء التمرين بنجاح',
    'exercise_updated' => 'تم تحديث التمرين بنجاح',
    'exercise_deleted' => 'تم حذف التمرين بنجاح',
    'exercise_not_found' => 'التمرين غير موجود',
    'unauthorized' => 'إجراء غير مصرح به',
    'failed_to_create' => 'فشل الإنشاء: :error',
    'failed_to_retrieve' => 'فشل الاسترجاع: :error',
    'failed_to_delete' => 'فشل الحذف: :error',

    // ... Existing messages ...
    'workout_sessions_retrieved' => 'تم استرجاع جلسات التمارين بنجاح',
    'workout_session_retrieved' => 'تم استرجاع جلسة التمرين بنجاح',
    'workout_session_created' => 'تم إنشاء جلسة التمرين بنجاح',
    'workout_session_updated' => 'تم تحديث جلسة التمرين بنجاح',
    'workout_session_completed' => 'تم إكمال جلسة التمرين بنجاح',
    'workout_session_skipped' => 'تم تخطي جلسة التمرين بنجاح',
    'workout_session_deleted' => 'تم حذف جلسة التمرين بنجاح',
    'workout_session_not_found' => 'جلسة التمرين غير موجودة',
    'incomplete_main_exercises_warning' => 'بعض التمارين الأساسية لم تكتمل. هل أنت متأكد أنك تريد إنهاء الجلسة؟',
    'no_previous_sessions' => 'لا توجد جلسات سابقة متاحة',
    'validation_failed_plan' => 'لا يمكن اختيار خطة محددة مسبقًا وخطة مخصصة في نفس الوقت.',
];