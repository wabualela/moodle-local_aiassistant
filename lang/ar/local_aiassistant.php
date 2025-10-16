<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'local_aiassistant', language 'ar', branch 'MOODLE_20_STABLE'
 *
 * @package   local_aiassistant
 * @copyright 2025, Wail Abualela wailabualela@alborhan.sa
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'مساعد الذكاء الاصطناعي';
$string['open_assistant'] = 'فتح مساعد الذكاء الاصطناعي';
$string['need_help'] = 'هل تحتاج إلى مساعدة؟';
$string['ai_labeltext'] = 'هل تحتاج إلى مساعدة؟';
$string['assistant_name'] = 'دعم Moodle بالذكاء الاصطناعي';
$string['options'] = 'خيارات';
$string['close'] = 'إغلاق';
$string['clearhistory'] = 'مسح سجل المحادثة';
$string['welcome_message'] = 'مرحبًا! أنا مساعد Moodle الذكي. كيف يمكنني مساعدتك؟';
$string['message_placeholder'] = 'أرسل رسالة...';
$string['attach_file'] = 'إرفاق ملف';
$string['send'] = 'إرسال رسالة';
$string['powered_by'] = 'مدعوم من Moodle AI';
$string['language_selection'] = 'ما هي اللغة التي تريد المتابعة بها؟';
$string['anything_else'] = 'هل هناك أي شيء آخر تود معرفته؟';
$string['assistantdisabled'] = 'المساعد غير متاح حاليًا. يرجى التواصل مع مسؤول الموقع.';
$string['aiintegrationerror'] = 'خدمة الذكاء الاصطناعي غير متاحة الآن. حاول مرة أخرى لاحقًا أو تواصل مع مسؤول الموقع.';
$string['error_generic'] = 'عذرًا، لم نتمكن من الاتصال بالمساعد. يرجى المحاولة مرة أخرى لاحقًا.';
$string['assistantid_required'] = 'معرّف المساعد مطلوب عند استخدام وضع واجهة برمجة المساعدين (Assistants API). يرجى تكوينه في إعدادات الإضافة.';
$string['assistantid_invalid_format'] = 'صيغة معرّف المساعد غير صحيحة. يجب أن يبدأ بـ "asst_" متبوعًا بأحرف وأرقام.';
$string['assistantinfo_no_apikey'] = 'لم يتم تكوين مفتاح OpenAI API. يرجى تكوينه في إدارة الموقع > الذكاء الاصطناعي > موفري الذكاء الاصطناعي.';
$string['loading'] = 'جاري التحميل...';
$string['fetching_assistants'] = 'جاري جلب المساعدين من OpenAI...';
// عناوين الإعدادات.
$string['generalheading'] = 'الإعدادات العامة';
$string['appearanceheading'] = 'إعدادات المظهر';
$string['integrationheading'] = 'تكامل الذكاء الاصطناعي';

// الإعدادات العامة.
$string['enable'] = 'تفعيل مساعد الذكاء الاصطناعي';
$string['enable_desc'] = 'تفعيل أو تعطيل مساعد الذكاء الاصطناعي لجميع المستخدمين';

// إعدادات المظهر.
$string['assistantname'] = 'اسم المساعد';
$string['assistantname_desc'] = 'الاسم المعروض في رأس المحادثة';
$string['fabcolor'] = 'لون زر FAB';
$string['fabcolor_desc'] = 'لون زر الإجراء العائم';
$string['fabicon'] = 'أيقونة FAB';
$string['fabicon_desc'] = 'قم بتحميل أيقونة مخصصة لزر FAB (PNG أو JPG أو SVG). الحجم الموصى به: 40x40 بكسل';
$string['welcomemessage'] = 'رسالة الترحيب';
$string['welcomemessage_desc'] = 'رسالة الترحيب التي تظهر للمستخدمين عند فتح المحادثة. يمكنك استخدام تنسيق HTML.';

// تكامل الذكاء الاصطناعي.
$string['integrationheading_desc'] = 'يتم الآن إدارة بيانات الاعتماد والنماذج وحدود الاستخدام من خلال موفري الذكاء الاصطناعي في مودل. انتقل إلى إدارة الموقع > الذكاء الاصطناعي لإعداد موفر OpenAI ثم قم بتمكين الإجراءات التي تريد إتاحتها للمستخدمين.';
$string['apimode'] = 'وضع واجهة برمجة التطبيقات';
$string['apimode_desc'] = 'اختر ما إذا كنت تريد استخدام واجهة برمجة التطبيقات القياسية (Completion API) أو واجهة برمجة المساعدين (Assistants API)';
$string['apimode_completion'] = 'واجهة برمجة التطبيقات القياسية (Completion API)';
$string['apimode_assistant'] = 'واجهة برمجة المساعدين (Assistants API) - متقدم';
$string['assistantid'] = 'مساعد OpenAI';
$string['assistantid_desc'] = 'اختر مساعد OpenAI الخاص بك من القائمة المنسدلة. يتم جلب القائمة تلقائيًا من حساب OpenAI الخاص بك. قم بإنشاء مساعد على platform.openai.com إذا لم تقم بذلك بعد.';
$string['prompt'] = 'موجه النظام';
$string['prompt_desc'] = 'موجه النظام الذي يحدد سلوك المساعد (يُستخدم فقط مع وضع واجهة برمجة التطبيقات القياسية)';
$string['defaultprompt'] = 'أنت مساعد ذكاء اصطناعي مفيد لنظام إدارة التعلم Moodle. قدم المساعدة الواضحة والموجزة والدقيقة للمستخدمين.';
