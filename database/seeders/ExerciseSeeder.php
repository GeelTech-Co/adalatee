<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\ExerciseTranslation;
use Illuminate\Database\Seeder;

class ExerciseSeeder extends Seeder
{
    public function run()
    {
        // Exercise 1: Cable Seated Chest Press
        $exercise1 = Exercise::create([
            'name' => 'Cable Seated Chest Press',
            'type' => 'main',
            'muscle_group' => 'Chest',
            'secondary_muscles' => 'Front Deltoid',
            'image_url' => null,
            'animation_url' => null,
        ]);

        ExerciseTranslation::create([
            'exercise_id' => $exercise1->id,
            'locale' => 'en',
            'description' => 'Cable chest flyes is an isolation exercise for the chest and front deltoids. Compared to dumbbell chest flyes, the resistance curve in cable flyes is more evenly distributed, and you have more of a constant load on your chest muscles throughout the whole range of motion.',
            'instructions' => "1. Place a bench between two cable pulleys and adjust the pulleys so they are at chest/shoulder height when you are seated.\n2. Grab the handles with a neutral grip and lean against the upright backrest.\n3. Bring your arms together in front of your chest in a controlled motion, focusing on squeezing your chest muscles. Keep your elbows slightly bent throughout the movement.\n4. Pause briefly at the top position.\n5. Slowly lower your arms back to the starting position while maintaining control of the movement.\n6. Repeat for reps.",
            'precautions' => 'Keep the movement slow and controlled to avoid injury.',
        ]);

        ExerciseTranslation::create([
            'exercise_id' => $exercise1->id,
            'locale' => 'ar',
            'description' => 'تمرين الكابل فلاي للصدر هو تمرين عزل يستهدف عضلات الصدر والجزء الأمامي من الكتف. مقارنة بتمرين الفلاي بالدمبل، يوفر الكابل مقاومة متساوية تقريبًا على طول الحركة، مما يبقي الحمل ثابتًا على عضلات الصدر طوال المدى.',
            'instructions' => "1. ضع مقعدًا بين بكرتين للكابل، واضبط ارتفاع المقابض بحيث تكون بمستوى الصدر أو الكتف وأنت جالس.\n2. أمسك المقابض بقبضة حيادية (راحة اليدين متقابلتين) واتكئ على مسند المقعد الخلفي.\n3. قرّب ذراعيك إلى الأمام باتجاه منتصف الصدر بحركة متحكم بها، مع التركيز على شد عضلات الصدر. حافظ على انثناء بسيط في المرفقين طوال الحركة.\n4. توقف لحظة قصيرة عند نقطة انقباض العضلات.\n5. أعد ذراعيك ببطء إلى وضعية البداية مع التحكم في الحركة.\n6. كرر التمرين حسب عدد التكرارات المطلوب.",
            'precautions' => 'اجعل الحركة بطيئة ومتحكم بها لتجنب الإصابة.',
        ]);

        // Exercise 2: Leg Extension
        $exercise2 = Exercise::create([
            'name' => 'Leg Extension',
            'type' => 'main',
            'muscle_group' => 'Quads',
            'secondary_muscles' => null,
            'image_url' => null,
            'animation_url' => null,
        ]);

        ExerciseTranslation::create([
            'exercise_id' => $exercise2->id,
            'locale' => 'en',
            'description' => 'Leg extensions target the quadriceps muscles. The exercise involves extending the knees against resistance to strengthen the quads.',
            'instructions' => "1. Adjust the machine so that you are correctly positioned. Your knees should be in line with the machine’s joint.\n2. Extend your knees with control, until they are completely straight.\n3. Slowly lower the weight again.",
            'precautions' => "• Adding too much weight can increase the risk of injury and lead to using excessive momentum. Keep the movement slow and controlled.\n• Not activating your core can cause discomfort and shift focus from your quads. Keep your back pressed firmly against the backrest.\n• Avoid short range of motion. Fully extend your legs for maximum effectiveness without compromising form.",
        ]);

        ExerciseTranslation::create([
            'exercise_id' => $exercise2->id,
            'locale' => 'ar',
            'description' => 'تمرين تمديد الساقين يستهدف عضلات الفخذ الأمامية (الكوادرسبس). يتضمن التمرين تمديد الركبتين ضد مقاومة لتقوية العضلات.',
            'instructions' => "1. اضبط الجهاز بحيث يكون وضعك صحيحًا، ويكون مفصل ركبتك بمحاذاة محور دوران الجهاز.\n2. مدّ ركبتيك للأمام بشكل متحكم به حتى تصبح ساقاك مستقيمتين تمامًا.\n3. أعد الوزن ببطء إلى وضعية البداية.",
            'precautions' => "• زيادة الوزن بشكل مبالغ فيه يمكن أن يزيد خطر الإصابة ويؤدي إلى استخدام قوة اندفاع. اجعل الحركة بطيئة ومتحكم بها.\n• إهمال شد عضلات البطن قد يسبب انزعاجًا ويقوس أسفل الظهر، مما يحول الجهد بعيدًا عن عضلات الفخذ. أبق ظهرك ملتصقًا جيدًا بمسند المقعد.\n• تجنب مدى حركة قصير. مدّ ساقيك بالكامل لتحقيق أقصى فاعلية دون التأثير على وضعية الجسم.",
        ]);
    }
}