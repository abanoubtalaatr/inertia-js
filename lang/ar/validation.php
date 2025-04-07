<?php

return [
    'accepted' => 'يجب قبول :attribute.',
    'current_password' => 'كلمة المرور الحالية ',
    'active_url' => ':attribute لا يُمثّل رابطًا صحيحًا.',
    'after' => 'يجب على :attribute أن يكون تاريخًا لاحقًا للتاريخ :date.',
    'after_or_equal' => ':attribute يجب أن يكون تاريخاً لاحقاً أو مطابقاً للتاريخ :date.',
    'alpha' => 'يجب أن لا يحتوي :attribute سوى على حروف.',
    'alpha_dash' => 'يجب أن لا يحتوي :attribute سوى على حروف، أرقام ومطّات.',
    'alpha_num' => 'يجب أن يحتوي :attribute على حروفٍ وأرقامٍ فقط.',
    'array' => 'يجب أن يكون :attribute ًمصفوفة.',
    'before' => 'يجب على :attribute أن يكون تاريخًا سابقًا للتاريخ :date.',
    'before_or_equal' => ':attribute يجب أن يكون تاريخا سابقا أو مطابقا للتاريخ :date.',
    'between' => [
        'numeric' => 'يجب أن تكون قيمة :attribute بين :min و :max.',
        'file' => 'يجب أن يكون حجم الملف :attribute بين :min و :max كيلوبايت.',
        'string' => 'يجب أن يكون عدد حروف النّص :attribute بين :min و :max.',
        'array' => 'يجب أن يحتوي :attribute على عدد من العناصر بين :min و :max.',
    ],
    'boolean' => 'يجب أن تكون قيمة :attribute إما true أو false .',
    'confirmed' => 'حقل التأكيد غير مُطابق للحقل :attribute.',
    'date' => ':attribute ليس تاريخًا صحيحًا.',
    'date_equals' => 'يجب أن يكون :attribute مطابقاً للتاريخ :date.',
    'date_format' => 'لا يتوافق :attribute مع الشكل :format.',
    'different' => 'يجب أن يكون الحقلان :attribute و :other مُختلفين.',
    'digits' => 'يجب أن يحتوي :attribute على :digits رقمًا/أرقام.',
    'digits_between' => 'يجب أن يحتوي :attribute بين :min و :max رقمًا/أرقام .',
    'dimensions' => 'الـ :attribute يحتوي على أبعاد صورة غير صالحة.',
    'distinct' => 'للحقل :attribute قيمة مُكرّرة.',
    'email' => 'يجب أن يكون :attribute عنوان بريد إلكتروني صحيح البُنية.',
    'ends_with' => 'يجب أن ينتهي :attribute بأحد القيم التالية: :values',
    'exists' => 'القيمة المحددة :attribute غير موجودة.',
    'file' => 'الـ :attribute يجب أن يكون ملفا.',
    'filled' => ':attribute إجباري.',
    'gt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أكبر من :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أكبر من :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النّص :attribute أكثر من :value حروفٍ/حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على أكثر من :value عناصر/عنصر.',
    ],
    'gte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :value حروفٍ/حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :value عُنصرًا/عناصر.',
    ],
    'image' => 'يجب أن يكون :attribute صورةً.',
    'in' => ':attribute غير موجود.',
    'in_array' => ':attribute غير موجود في :other.',
    'integer' => 'يجب أن يكون :attribute عددًا صحيحًا.',
    'ip' => 'يجب أن يكون :attribute عنوان IP صحيحًا.',
    'ipv4' => 'يجب أن يكون :attribute عنوان IPv4 صحيحًا.',
    'ipv6' => 'يجب أن يكون :attribute عنوان IPv6 صحيحًا.',
    'json' => 'يجب أن يكون :attribute نصآ من نوع JSON.',
    'lt' => [
        'numeric' => 'يجب أن تكون قيمة :attribute أصغر من :value.',
        'file' => 'يجب أن يكون حجم الملف :attribute أصغر من :value كيلوبايت.',
        'string' => 'يجب أن يكون طول النّص :attribute أقل من :value حروفٍ/حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على أقل من :value عناصر/عنصر.',
    ],
    'lte' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :value.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :value كيلوبايت.',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :value حروفٍ/حرفًا.',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :value عناصر/عنصر.',
    ],
    'max' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أصغر من :max.',
        'file' => 'يجب أن لا يتجاوز حجم الملف :attribute :max كيلوبايت.',
        'string' => 'يجب أن لا يتجاوز طول النّص :attribute :max حروفٍ/حرفًا.',
        'array' => 'يجب أن لا يحتوي :attribute على أكثر من :max عناصر/عنصر.',
    ],
    'mimes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'mimetypes' => 'يجب أن يكون ملفًا من نوع : :values.',
    'min' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية أو أكبر من :min.',
        'file' => 'يجب أن يكون حجم الملف :attribute على الأقل :min كيلوبايت.',
        'string' => 'يجب أن يكون طول النص :attribute على الأقل :min حروفٍ/حرفًا.',
        'array' => 'يجب أن يحتوي :attribute على الأقل على :min عُنصرًا/عناصر.',
    ],
    'not_in' => 'العنصر :attribute غير صحيح.',
    'not_regex' => 'صيغة :attribute غير صحيحة.',
    'numeric' => 'يجب على :attribute أن يكون رقمًا.',
    'present' => 'يجب تقديم :attribute.',
    'regex' => 'صيغة :attribute .غير صحيحة.',
    'required' => ':attribute مطلوب.',
    'required_if' => ':attribute مطلوب في حال ما إذا كان :other يساوي :value.',
    'required_unless' => ':attribute مطلوب في حال ما لم يكن :other يساوي :values.',
    'required_with' => ':attribute مطلوب إذا توفّر :values.',
    'required_with_all' => ':attribute مطلوب إذا توفّر :values.',
    'required_without' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'required_without_all' => ':attribute مطلوب إذا لم يتوفّر :values.',
    'same' => 'يجب أن يتطابق :attribute مع :other.',
    'size' => [
        'numeric' => 'يجب أن تكون قيمة :attribute مساوية لـ :size.',
        'file' => 'يجب أن يكون حجم الملف :attribute :size كيلوبايت.',
        'string' => 'يجب أن يحتوي النص :attribute على :size حروفٍ/حرفًا بالضبط.',
        'array' => 'يجب أن يحتوي :attribute على :size عنصرٍ/عناصر بالضبط.',
    ],
    'starts_with' => 'يجب أن يبدأ :attribute بأحد القيم التالية: :values',
    'string' => 'يجب أن يكون :attribute نصًا.',
    'timezone' => 'يجب أن يكون :attribute نطاقًا زمنيًا صحيحًا.',
    'unique' => 'قيمة :attribute مُستخدمة من قبل.',
    'uploaded' => 'فشل في تحميل الـ :attribute.',
    'url' => 'صيغة الرابط :attribute غير صحيحة.',
    'uuid' => ':attribute يجب أن يكون بصيغة UUID سليمة.',
    'current_password' => 'كلمة المرور الحالية',
    'You must add at least one level.' => 'يجب إضافة مستوى واحد على الأقل.',
    'Each level must have a valid level type.' => 'يجب أن يحتوي كل مستوى على نوع مستوى صالح.',
    'Each level must have a number of labors.' => 'يجب أن يحتوي كل مستوى على عدد العمال.',
    'Each level must have a price.' => 'يجب أن يحتوي كل مستوى على سعر.',
    'duplicate_level_data' => 'هذا المستوى موجود بالفعل بنفس عدد العمال والسعر.',
    'invalid_level_data' => 'معلومات المستوى غير صالحة.',
    'levels_required' => 'مستوي الخدمه مطلوب .',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'birth_date' => [
            'before' => 'يجب أن يكون تاريخ الميلاد سابقا لتاريخ اليوم',
        ],
        'current_password' => [
            'current_password' => 'كلمة المرور الحالية التي أدخلتها غير صحيحة.', // رسالة خطأ مخصصة
        ],
        'images' => [
            'required' => 'حقل الصور  العامة مطلوب.',
            'invalid' => 'يجب أن تكون الصور ملف صورة صالح (jpeg، png، jpg، gif، svg).',
        ],
        'image' => [
            'required' => ' حقل الصورة   مطلوب.',
            'invalid' => 'يجب أن تكون الصورة ملف صورة صالح (jpeg، png، jpg، gif، svg).',
        ],
        'private_images' => [
            'required' => 'حقل الصور الخاصة مطلوب.',
            'invalid' => 'يجب أن تكون الصور الخاصة ملف صورة صالح (jpeg، png، jpg، gif، svg).',
        ],
        'design_images' => [
            'required' => 'حقل صور التصميم مطلوب.',
            'invalid' => 'يجب أن تكون صور التصميم ملف صورة صالح (jpeg، png، jpg، gif، svg).',
        ],
        'whatsapp' => [
            'required' => 'رقم الواتساب مطلوب.',
            'string' => 'يجب أن يكون رقم الواتساب  نصا.',
            'max' => 'يجب أن لا يزيد رقم الواتساب عن :max حروفٍ/حرفًا.',
            'regex' => 'صيغة رقم الواتساب غير صحيحة.',
        ],
        'phone' => [
            'regex' => 'يجب أن يبدأ رقم الهاتف بـ +9665 ويتبعه 8 أرقام.',
        ],
        'mobile' => [
            'regex' => 'يجب أن يبدأ رقم الهاتف بـ +9665 ويتبعه 8 أرقام.',
        ],
        'password' => [
            'regex' => 'يجب أن تحتوي كلمة المرور على حرف كبير واحد على الأقل (مثل: A) وحرف خاص واحد (مثل: @).',
        ],
    ],

    'attributes' => [
        'selectedRoles' => 'أدوار المستخدم',
        'password' => 'كلمة المرور',
        'password_confirmation' => 'تـأكيد كلمة المرور',
        'birth_date' => 'تاريخ الميلاد',
        'today' => 'اليوم',
        'current_password' => 'كلمة المرور الحالية',
        'photo' => 'الصورة',
        'mobile' => 'رقم الجوال',
        'email' => 'البريد الإلكتروني',
        'name' => 'الإسم',
        'national_id' => 'رقم الهوية',

        'address' => 'العنوان',
        'address_ar' => 'العنوان بالعربية',
        'address_en' => 'العنوان بالإنجليزية',

        'amount' => 'القيمة',
        'phone' => 'الجوال',
        'new_password' => 'كلمة المرور الجديدة',
        'new_password_confirmation' => 'تأكيد كلمة المرور الجديدة',
        'old_password' => 'كلمة المرور الحالية',

        'question_ar' => 'السؤال بالعربية',
        'question_en' => 'السؤال بالإنجليزية',
        'answer_ar' => 'الإجابة بالعربية',
        'answer_en' => 'الإجابة بالإنجليزية',
        'display_order' => 'ترتيب العرض',
        'fax_number' => 'رقم الفاكس',
        'website' => 'الموقع الإلكتروني',
        'description' => 'الوصف',

        'instagram' => 'إنستاجرام',
        'facebook' => 'فيس بوك',
        'snapchat' => 'سناب شات',
        'tiktok' => 'تيك توك',
        'twitter' => 'تويتر',

        'youtube' => 'يوتيوب',

        'password_confirmation' => 'تأكيد كلمة المرور',
        'whatsapp' => 'واتساب',
        'deny_reason' => 'سبب الرفض',
        'description_ar' => 'الوصف بالعربية',
        'description_en' => 'الوصف بالإنجليزية',

        'image' => 'الصورة',

        'name_ar' => 'الإسم بالعربية',
        'name_en' => 'الإسم بالإنجليزية',

        'status' => 'الحالة',
        'details_ar' => 'التفاصيل بالعربية',
        'details_en' => 'التفاصيل بالإنجليزية',
        'address_id' => 'العنوان',

        'from_date' => 'تاريخ البداية',
        'to_date' => 'تاريخ النهاية',
        'avatar' => 'الصورة',

        'title_ar' => 'العنوان بالعربية',
        'title_en' => 'العنوان الإنجليزية',

        'cover_pic' => 'صورة الغلاف',

        'pic' => 'الصورة',

        'notes_ar' => 'ملاحظات بالعربية',
        'notes_en' => 'ملاحظات بالإنجليزية',

        'latitude' => 'خط العرض',
        'longitude' => 'خط الطول',
        'stars' => 'عدد النجوم',
        'job_role' => 'المسمى الوظيفي',
        'registration_number' => 'رقم التسجيل',
        'tax_number' => 'الرقم الضريبي',
        'star_classification_certificate' => 'شهادة تصنيف النجوم',
        'compliance_proof' => 'إثبات الامتثال',
        'logo' => 'الشعار',
        'features' => 'المميزات',
        'feature' => 'ميزة',
        'images' => 'الصور',
        'image' => 'صورة',
        'social_links' => 'روابط التواصل الاجتماعي',
        'facebook_link' => 'رابط فيسبوك',
        'instagram_link' => 'رابط انستجرام',
        'twitter_link' => 'رابط تويتر',
        'linkedIn' => 'رابط لينكد إن',
        'bank' => 'البنك',
        'bank_account_holder_name' => 'اسم صاحب الحساب البنكي',
        'bank_account_number' => 'رقم الحساب البنكي',
        'iban' => 'رقم الآيبان',
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'mobile' => 'رقم الجوال',
        'password' => 'كلمة المرور',
        'address' => 'العنوان',
        'commercial_register' => 'السجل التجاري',
    ],

    'reasons' => [
        'complete_profile' => 'يجب أن يكون الحساب مكتمل.',
        'bank_info' => 'يجب أن يكون الحساب مربوط ببيانات البنك.',
    ],

];
