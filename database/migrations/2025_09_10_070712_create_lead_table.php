<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fields should be inserted in to lead table
        // "ad_id": "5810927",
        // "UID": "220827",
        // "type": "land",
        // "propty_type": "Bare Land",
        // "service_type": "",
        // "street": "",
        // "city": "Malabe",
        // "heading": "Bare land for sale in Malabe 250 meters from the Colombo-Malabe Main Road",
        // "desc": "\u003Cbr /\u003E\n\u003Cbr /\u003E\nLocated just less than 250 meters from the Colombo-Malabe Main Road\u003Cbr /\u003E\nhighly secured and residential area.\u003Cbr /\u003E\n\u003Cbr /\u003E\nProperty Features:\u003Cbr /\u003E\n\u003Cbr /\u003E\n- Surrounded by all essential urban amenities, including:\u003Cbr /\u003E\n- National &#38; International Schools\u003Cbr /\u003E\n- Banks, Supermarkets, Pharmacies, Hospitals\u003Cbr /\u003E\n- Close to Laugfs and other key facilities\u003Cbr /\u003E\n\u003Cbr /\u003E\n- This well-maintained property offers a clear deed and is situated in a safe and peaceful neighborhood.\u003Cbr /\u003E\n\u003Cbr /\u003E\nPrice (negotiable): (20 perch) buildable land Rs. 40 million\u003Cbr /\u003E\n– Don’t miss out on this fantastic opportunity! Contact us for more details or to schedule a viewing.\u003Cbr /\u003E\n\u003Cbr /\u003E\nදේපල විස්තර\u003Cbr /\u003E\nකොළඹ-මලබේ ප්‍රධාන මාර්ගයේ සිට මීටර් 250 කටත් අඩු දුරකින් පිහිටි මෙම දේපල, විශිෂ්ට පහසුවක් සහ ප්‍රවේශයක් සපයයි.\u003Cbr /\u003E\n\u003Cbr /\u003E\nපහත සඳහන් සියලු අත්‍යවශ්‍ය නාගරික පහසුකම් වලින් වටවී ඇත:\u003Cbr /\u003E\nජාතික සහ ජාත්‍යන්තර පාසල්\u003Cbr /\u003E\nබැංකු, සුපිරි වෙළඳසැල්, ඖෂධහල්, රෝහල්\u003Cbr /\u003E\nLaugfs සහ අනෙකුත් ප්‍රධාන පහසුකම් වලට සමීපව\u003Cbr /\u003E\nමෙම මනාව නඩත්තු කරන ලද දේපල පැහැදිලි ඔප්පු සහිතව, ආරක්ෂිත සහ සාමකාමී පරිසරයක පිහිටා ඇත.\u003Cbr /\u003E\nමිල (සාකච්ඡා කරගත හැක)\u003Cbr /\u003E\n\u003Cbr /\u003E\nමෙම විශිෂ්ට අවස්ථාව අතපසු කර නොගන්න! වැඩි විස්තර සඳහා හෝ නැරඹීමක් සූදානම් කර ගැනීමට අප හා සම්බන්ධ වන්න\u003Cbr /\u003E\n",
        // "submit_date": "2025-09-10 12:10:20",
        // "posted_date": "2025-09-10 12:10:20",
        // "price": "40000000",
        // "alt_price": "0",
        // "alt_currency": "$",
        // "price_type": "",
        // "price_monthly": "0",
        // "price_land_pp": "2000000",
        // "price_land_pa": "0",
        // "price_land_total": "40000000",
        // "price_sqft": null,
        // "land_s_l": "S",
        // "min_term_days": "0",
        // "comm_type": "",
        // "agent_ref": "",
        // "agent_page_ref": null,
        // "pic": "y",
        // "pic_count": null,
        // "pics_link": "",
        // "youtube_link": "",
        // "video_link": null,
        // "360_image_link": null,
        // "contact_type": "Owner",
        // "contact_name": "N Walpitagamage ",
        // "tel": null,
        // "email": "parap@sbcglobal.net",
        // "avail": "Available Now",
        // "lat": "6.906079000000000000",
        // "lng": "79.969627000000000000",
        // "zoom": null,
        // "last_ip": null,
        // "hits": null,
        // "was_active": "0",
        // "priority": "0",
        // "verified_ad": null,
        // "prev_approved": null,
        // "blocked": "N",
        // "is_block_house": "N",
        // "is_prime": null,
        // "is_invest": "N",
        // "invest_avg": "0",
        // "poster_ip": "172.71.124.116",
        // "is_active": "3",
        // "is_showcase": null,
        // "boosted_count": "0",
        // "last_edited": "0000-00-00 00:00:00",
        // "current_page_id": null,
        // "token": null,
        // "developer_name": "",
        // "company_address": "",
        // "matching_data": "",
        // "block_type": "",
        // "block_reason": "",
        // "source": "NEW AD POST",
        // "apartment_id": "0",
        // "deactivated_by_system": "0",
        // "deactivate_messge_send_date": null,
        // "is_import": "1",
        // "SKU": null,
        // "popup_selector": "N",
        // "number_review": "N",
        // "is_trending": "0",
        // "was_trending": "0",
        // "trending_date": null,
        // "price_validate": "Not Too Low",
        // "auto_boost_updated": null,
        // "is_sponsored": "N",
        // "video_thumb_link": null,
        // "is_whatsapp_chat": "N",
        // "whatsapp_chat_addon_id": null,
        // "hot_deals": "N",
        // "agree_to_share": "N",
        // "spam_blocked": "0",
        // "lowest_page_id": null,
        // "is_stats_generated": "N",
        // "house_post_url": "bare-land-for-sale-in-malabe-250-meters-from-the-colombo-malabe-main-road-5810927",
        // "is_spam_checked": "Y",
        // "adv_feat_dev_id": null,
        // "price_marcket_percentage": null,
        // "multiad_exp_date": null
        Schema::create('lead', function (Blueprint $table) {
            $table->id();
            $table->string('ad_id')->unique();
            $table->string('cust_id');
            $table->string('user_id')->nullable();
            $table->string('type')->nullable();
            $table->string('propty_type')->nullable();
            $table->string('service_type')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('heading')->nullable();
            $table->text('desc')->nullable();
            $table->dateTime('submit_date')->nullable();    
            $table->dateTime('posted_date')->nullable();
            $table->bigInteger('price')->nullable();
            $table->bigInteger('alt_price')->nullable();
            $table->string('alt_currency')->nullable();
            $table->string('price_type')->nullable();
            $table->bigInteger('price_monthly')->nullable();
            $table->bigInteger('price_land_pp')->nullable();
            $table->bigInteger('price_land_pa')->nullable();
            $table->bigInteger('price_land_total')->nullable();
            $table->string('price_sqft')->nullable();
            $table->string('land_s_l')->nullable();
            $table->integer('min_term_days')->nullable();
            $table->string('comm_type')->nullable();
            $table->string('agent_ref')->nullable();
            $table->string('agent_page_ref')->nullable();
            $table->string('pic')->nullable();
            $table->integer('pic_count')->nullable();
            $table->string('pics_link')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('video_link')->nullable();
            $table->string('360_image_link')->nullable();
            $table->string('contact_type')->nullable();
            $table->string('contact_name')->nullable();
            $table->string('tel')->nullable();
            $table->string('email')->nullable();
            $table->string('avail')->nullable();
            $table->string('lat')->nullable();
            $table->string('lng')->nullable();
            $table->string('zoom')->nullable();
            $table->string('last_ip')->nullable();
            $table->integer('hits')->nullable();
            $table->string('was_active')->nullable();
            $table->string('priority')->nullable();
            $table->string('verified_ad')->nullable();
            $table->string('prev_approved')->nullable();
            $table->string('blocked')->nullable();
            $table->string('is_block_house')->nullable();
            $table->string('is_prime')->nullable();
            $table->string('is_invest')->nullable();
            $table->bigInteger('invest_avg')->nullable();
            $table->string('poster_ip')->nullable();
            $table->string('is_active')->nullable();
            $table->string('is_showcase')->nullable();
            $table->integer('boosted_count')->nullable();
            $table->dateTime('last_edited')->nullable();
            $table->string('current_page_id')->nullable();
            $table->string('token')->nullable();
            $table->string('developer_name')->nullable();
            $table->string('company_address')->nullable();
            $table->text('matching_data')->nullable();
            $table->string('block_type')->nullable();
            $table->string('block_reason')->nullable();
            $table->string('source')->nullable();
            $table->string('apartment_id')->nullable();
            $table->string('deactivated_by_system')->nullable();
            $table->dateTime('deactivate_messge_send_date')->nullable();
            $table->string('is_import')->nullable();
            $table->string('SKU')->nullable();
            $table->string('popup_selector')->nullable();
            $table->string('number_review')->nullable();
            $table->string('is_trending')->nullable();
            $table->string('was_trending')->nullable();
            $table->dateTime('trending_date')->nullable();
            $table->string('price_validate')->nullable();
            $table->dateTime('auto_boost_updated')->nullable();
            $table->string('is_sponsored')->nullable();
            $table->string('video_thumb_link')->nullable();
            $table->string('is_whatsapp_chat')->nullable();
            $table->string('whatsapp_chat_addon_id')->nullable();
            $table->string('hot_deals')->nullable();
            $table->string('agree_to_share')->nullable();
            $table->string('spam_blocked')->nullable();
            $table->string('lowest_page_id')->nullable();
            $table->string('is_stats_generated')->nullable();
            $table->string('house_post_url')->nullable();
            $table->string('is_spam_checked')->nullable();
            $table->string('adv_feat_dev_id')->nullable();
            $table->string('price_marcket_percentage')->nullable();
            $table->dateTime('multiad_exp_date')->nullable();       
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead');
    }
};
