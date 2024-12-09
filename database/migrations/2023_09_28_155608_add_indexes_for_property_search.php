<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesForPropertySearch extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('properties', function (Blueprint $table) { 
            $table->index('id');
            $table->index('old_id');
            $table->index('slug');
            $table->index('property_ref_field'); 
            $table->index('market_status_field'); 
            $table->index('property_type_id_field'); 
            $table->index('property_status_id_field');
            $table->index('market_type_field');
            $table->index('locality_id_field');
            $table->index('price_field');
            $table->index('date_available_field');
            $table->index('sole_agents_field');
            $table->index('property_block_id_field');
            $table->index('bedrooms_field');
            $table->index('bathrooms_field');
            $table->index('is_featured_field');
            $table->index('area_field');
            $table->index('weight_field');
            $table->index('show_in_searches');
            $table->index('date_off_market_field');
            $table->index('user_id');
            $table->index('status'); 
            $table->index('region_field');
            $table->index('created_at'); 
            $table->index('to_synch');
        });
        
        Schema::table('consultants', function (Blueprint $table) { 
            $table->index('id');
            $table->index('full_name_field');
            $table->index('branch_id_field'); 
            $table->index('orig_consultant_image_src');
            $table->index('to_synch'); 
            $table->index('created_at'); 
            $table->index('image_status_field');
        });

        Schema::table('files', function (Blueprint $table) { 
            $table->index('file_name_field'); 
            $table->index('orig_image_src');
            $table->index('created_at'); 
            $table->index('image_status_field'); 
        });

        Schema::table('features', function (Blueprint $table) { 
            $table->index('feature_value');
            $table->index('created_at'); 
        });

        Schema::table('propertytype', function (Blueprint $table) { 
            $table->index('description');
            $table->index('created_at'); 
        });

        Schema::table('property_status', function (Blueprint $table) { 
            $table->index('description');
            $table->index('created_at'); 
        });

        Schema::table('locality', function (Blueprint $table) { 
            $table->index('locality_name'); 
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('properties', function (Blueprint $table) { 
            $table->dropIndex(['id']);
            $table->dropIndex(['old_id']);
            $table->dropIndex(['slug']);
            $table->dropIndex(['property_ref_field']);
            $table->dropIndex(['market_status_field']);
            $table->dropIndex(['property_type_id_field']);
            $table->dropIndex(['property_status_id_field']);
            $table->dropIndex(['market_type_field']); 
            $table->dropIndex(['locality_id_field']);
            $table->dropIndex(['price_field']);
            $table->dropIndex(['date_available_field']);
            $table->dropIndex(['sole_agents_field']);
            $table->dropIndex(['property_block_id_field']);
            $table->dropIndex(['bedrooms_field']);
            $table->dropIndex(['bathrooms_field']);
            $table->dropIndex(['is_featured_field']);
            $table->dropIndex(['area_field']);
            $table->dropIndex(['weight_field']);
            $table->dropIndex(['show_in_searches']);
            $table->dropIndex(['date_off_market_field']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['region_field']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['to_synch']); 
        });
        
        Schema::table('consultants', function (Blueprint $table) { 
            $table->dropIndex(['id']);
            $table->dropIndex(['full_name_field']);
            $table->dropIndex(['branch_id_field']);
            $table->dropIndex(['orig_consultant_image_src']);
            $table->dropIndex(['to_synch']);
            $table->dropIndex(['image_status_field']); 
            $table->dropIndex(['created_at']); 
        });

        Schema::table('files', function (Blueprint $table) { 
            $table->dropIndex(['file_name_field']);
            $table->dropIndex(['orig_image_src']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['image_status_field']);
        }); 

        Schema::table('features', function (Blueprint $table) { 
            $table->dropIndex(['feature_value']); 
            $table->dropIndex(['created_at']);
        });

        Schema::table('propertytype', function (Blueprint $table) { 
            $table->dropIndex(['description']); 
            $table->dropIndex(['created_at']);
        });

        Schema::table('property_status', function (Blueprint $table) { 
            $table->dropIndex(['description']); 
            $table->dropIndex(['created_at']);
        });

        Schema::table('locality', function (Blueprint $table) { 
            $table->dropIndex(['locality_name']); 
            $table->dropIndex(['created_at']);
        });
    }
} 