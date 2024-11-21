@extends('layouts.user', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
<link href="{{asset('backend/css/dropzone.min.css')}}" rel="stylesheet">
<script src="{{asset('backend/js/dropzone.min.js')}}"></script>
<style>
    .input-group {
    margin-bottom: 10px; /* Adjust this value to increase or decrease the gap */
}

.variant-field {
    margin-bottom: 15px; /* Gap between variant fields */
}

.modal-body .row-cards .form-imagecheck .form-imagecheck-input {position: absolute;left: 10%;top: 10%;width: 12%;height: 11%;z-index: 1;}
.form-imagecheck-image {max-width: 100%;width: 100%;opacity: 0.64;border-radius:10px;transition:0.5s;}
.form-imagecheck-image:hover {opacity: 1;}
.row-cards .form-imagecheck .form-imagecheck-input:checked ~ .form-imagecheck-figure img {opacity:1;border:1px solid #0054a6;}
.tags-input{list-style : none;border:1px solid #ccc;display:inline-block;padding:5px;height: 26px;font-size:14px;background:#f3f3f3;width: 600px;border-radius:2px;overflow:hidden;}
.tags-input li{float:left;}
.tags{background:#195FA6;padding:5px 20px 5px 8px;border-radius:2px;margin-right: 5px;position: relative;color:#fff;}
.tags i{position: absolute;right:6px;top:3px;width: 8px;height: 8px;content:'';cursor:pointer;opacity: .7;font-size:12px;}
.tags i:hover{opacity: 1;}
.tags-new input[type="text"]{border:0;margin: 0;padding: 0 0 0 3px;font-size: 14px;margin-top: 5px;background:transparent;}
.tags-new input[type="text"]:focus{outline:none;}
</style>
@endsection

@section('content')

<div class="container-fluid">
    <div class="row row-deck row-cards">
        <div class="col-sm-12 col-lg-12">
            <form action="{{ route('user.save.products',$store_id) }}" method="post"
                enctype="multipart/form-data" class="card">
                @csrf
                <div class="card-body">
                    @php
                     use App\User;
                            use App\Models\Plugin;
                            use App\Models\Extension;
                        
                            // Get the user's plan
                            $plan = User::where('user_id', Auth::user()->user_id)->first();
                            $active_plan = null; // Initialize active_plan
                            $appointment = null; // Initialize active_plan
                        
                            // Check if the user's plan exists
                            if ($plan) {
                                $active_plan = json_decode($plan->plan_details);
                            }
                            if(isset($active_plan))
                        {
                            $menu_data = DB::table('plugins')
                        ->where('plan_id', $active_plan->plan_id)
                            ->where(function ($query) {
                                $user_id = Auth::user()->user_id;
                                $query->where('user_id', $user_id) // Prefer plugins with matching user_id
                                      ->orWhere(function ($subquery) use ($user_id) {
                                          $subquery->whereNull('user_id') // Only use plan-based plugins if no user-specific plugin exists
                                                   ->whereNotExists(function ($innerQuery) use ($user_id) {
                                                       $innerQuery->select(DB::raw(1))
                                                                  ->from('plugins')
                                                                  ->whereColumn('plugins.plan_id', 'plan_id')
                                                                  ->where('user_id', $user_id);
                                                   });
                                      });
                            })
                            ->get();
                        }
                        else {
                            $menu_data = null;
                        }
                    $plugin = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Book An Appointment' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
                    if(isset($plugin))
                    {
                        $appointment = App\Models\Extension::where('card_id',$store_id)->where('plugin_id',$plugin->id)->where('is_deleted',0)->first();
                    }
                    @endphp
                    @if(isset($appointment) && $appointment->is_active == 1)
                    <div class="col-xl-12">
                        <h3 class="page-title my-3">{{ __('Products / Services') }}</h3>
                        <?php $i = 0; ?>
                        <div class='row' id="{{ $i }}">
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_status'>
                                        <strong>{{ __('Categories') }}</strong>
                                        <span class="text-danger">*</span> 
                                    </label>
                                    <a style="float: right;" href="{{ route('user.create.category', $business_card->card_id) }}">{{ __('Add Category') }}</a>
                                    <select name='categories[]' id='assign_category' class='form-control' required>
                                        <option value=''>Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value='{{ $category->category_id }}'>{{ __($category->category_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_status'><strong>{{ __('Sub Categories') }}</strong><span class = "text-danger">*</span></label>
                                    <a style="float: right;" href="{{ route('user.create.subcategory',$business_card->card_id) }}">{{__('Add Sub Category')}}</a>
                                    <select name='sub_category[]' id='assign_subcategory' class='form-control' required>
                                        <option value=''>Select Sub Category</option>
                                        {{-- <option value='0' >No Sub Category Available</option> --}}
                                        <!--@foreach ($sub_categories as $sub_category)-->
                                        <!--<option value='{{ $sub_category->sub_category_id }}'>{{ __($sub_category->sub_category_name) }}</option>-->
                                        <!--@endforeach-->
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6 d-none'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Product Badge') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='text' class='form-control' value="123" name='badge[]' placeholder='{{ __(' Product Badge') }}...' value="" required>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required'><strong>{{ __('Product / Service Image') }}</strong><span class = "text-danger">*</span></label>
                                    <div class='input-group mb-2' style="justify-content: space-between;align-items: center;">
                                        <input type='hidden' class='image1 media-model form-control'  name='product_image[]' placeholder='{{ __(' Product / Service Image') }}' value="" >
                                        <label class='mb-0'>
                                            <strong><span id="images_count_uploaded"> 0</span> Images has been uploaded.</strong>
                                         </label>
                                        <button class='btn btn-primary btn-md' type='button' onclick="openMedia(1)">{{ __('Choose image') }}</button>
                                    </div>
                                    <small>(Max 3 Images Allowed) (Image should be 800*800)</small></br>
                                    
                                    <small>Image Resize : <a href="https://imageresizer.com/" target="blank"><b>Click Here.</b></a></small>
                                </div>
                                <div id="iamges_preview" class="row">
                                    
                                   
                                 
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class = "row">
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required'><strong>{{ __('Product / Service Name') }}</strong><span class = "text-danger">*</span></label>
                                            <input type='text' class='form-control' name='product_name[]' placeholder='{{ __(' Product / Service Name') }}' value="" required>
                                        </div>
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required'><strong>{{ __('SKU') }}</strong></label>
                                            <input type='text' class='form-control'  pattern="[a-zA-Z0-9]+" name='sku[]' placeholder='{{ __('SKU') }}' value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-12 col-xl-12'>
                                <div class='mb-3'>
                                  <label class='form-label required'><strong>{{ __('Product / Service Description') }}</strong><span class="text-danger">*</span></label>
                                  <textarea class='form-control' maxlength="512" name='product_subtitle[]' id="product_subtitle"
                                    data-bs-toggle='autosize' placeholder='{{ __('Product Description') }}...' required></textarea>
                                  <div>
                                    <span id="current-title">0</span> / <span id="maximum-title">512</span> {{ __('characters') }}
                                  </div>
                                </div>
                              </div>
                              <div class='col-md-6 col-xl-6'>
                                <div class='row'>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Product / Service Documentation Title') }}</strong></label>
                                        <input type='text' class='form-control' name='vedio_title[]' placeholder='{{ __('Product / Service Documentation Title') }}' value="">
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Product / Servcie Documentation Icon') }}</strong></label>
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-jpg" value="jpg">
                                                <label class="form-check-label" for="icon-jpg">
                                                    <i class="fa fa-image"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-video" value="video">
                                                <label class="form-check-label" for="icon-video">
                                                    <i class="fa fa-play"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-document" value="document">
                                                <label class="form-check-label" for="icon-document">
                                                    <i class="fa fa-file"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Product / Service Documentation') }}</strong></label>
                                    <input type='text' class='form-control' name='vedio_link[]' value="" placeholder='{{ __(' Product / Service Documentation Link') }}'>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='row'>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label required'><strong>{{ __('No of units') }}</strong><span class = "text-danger">*</span></label>
                                        <input type='number' class='form-control' name='no_of_units[]'  min='1' placeholder='{{ __(' No of units') }}...' value="1" min='1' step='1' required>
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label required'><strong>{{ __('Unit') }}</strong></label>
                                        <select name="units[]" class="form-control" >
                                            <option value="">Select Unit</option>
                                            @foreach($units as $units_data)
                                                <option value="{{$units_data->unit_shortname}}" >{{$units_data->unit_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>   
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_price_status'><strong>{{__('Price Show/Hide') }}</strong><span class = "text-danger">*</span></label>
                                    <select name='product_price_status[]' id='product_price_status' class='form-control'>
                                        <option value='1' > {{ __('Show Price') }}</option>
                                        <option value='0' >{{ __('Hide Price') }}</option>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'><label class='form-label'><strong>{{ __('Regular Price') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='number' class='form-control' name='regular_price[]' min='1' placeholder='{{ __(' Regular Price') }}...' value="" min='1' step='.001'>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Sales Price') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='number' class='form-control' name='sales_price[]' min='1' step='.001' value="" placeholder='{{ __(' Sales Price') }}...'>
                                </div>
                            </div>            
                                               
                            </div>
                        </div>
                        <div class="row mb-4">
                            <div class="col text-center">
                                <a id="productBtn" class="btn btn-primary text-white">Add Product</a>
                                <a id="serviceBtn" class="btn btn-secondary text-white">Add Service</a>
                            </div>
                        </div>
                        <div class="col-xl-12" id = "productDetails" style = "display:none;">
                        <h5 class="page-title my-3">{{ __('Product Details') }}</h5>
                            <?php $i = 0; ?>
                            <div class='row' id="{{ $i }}">
                                @php
                    $plugin_variants = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Product Variants' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
    if(isset($plugin_variants))
    {
        $productvariants = App\Models\Extension::where('card_id', $store_id)
                                                   ->where('plugin_id', $plugin_variants->id)
                                                   ->where('is_deleted', 0)
                                                   ->first();
    }
                               
                               $plan = DB::table('users')
                                         ->where('user_id', Auth::user()->user_id)
                                         ->where('status', 1)
                                         ->first();
                               $plan_details = DB::table('plans')
                                                 ->where('plan_id', $plan->plan_id)
                                                 ->first();
                                                 $plugin_inventory = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Inventory Management' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
    if(isset($plugin_inventory))
    {
        $inventory = App\Models\Extension::where('plugin_id',$plugin_inventory->id)->where('card_id',$store_id)->where('is_deleted',0)->first();
    }
                               
                               $maxVariants = $plan_details->no_of_variants; // Get the number of variants allowed
                           @endphp
                        
                        @if (isset($productvariants) && $productvariants->is_active == 1)
                        {{-- <div class='col-md-6 col-xl-6'>
                            <label class='form-label'><strong>{{ __('Product Variants') }}</strong></label>
                           
                        </div>
                        <div class = "col-md-6 col-xl-6">
                            <div class = "row">
                                <div class = "col-md-6 col-xl-6">
                                  
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="variants_check" id="variants-no" value="No" onchange="toggleVariantFields()">
                                            <label class="form-check-label" for="icon-jpg">No</label>
                                        </div>
                                      
                                    
                                </div>
                                <div class = "col-md-6 col-xl-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="variants_check" id="variants-yes" value="Yes" onchange="toggleVariantFields()">
                                        <label class="form-check-label" for="icon-video">Yes</label>
                                    </div>
                                
                            </div>
                            </div>
                        </div>
                        <br><br> --}}
                        
                        <div class='col-md-12 col-xl-12' id="variant-fields-container">
                            <div class='row variant-field'>
                                <!-- Dynamic attribute fields -->
                                @if(isset($attributes) && $attributes->count() > 0)
                                    @foreach($attributes as $index => $attribute)
                                        <div class='col-md-3 col-xl-3'>
                                            <div class='mb-3'>
                                                <label class="form-label required"><strong>{{ $attribute->name }}</strong></label>
                                                <a style="float: right;" href="{{ route('user.add.attributes', $business_card->card_id) }}">{{ __('Add Attribute') }}</a>
                                                <select name="attributes[0][{{ $attribute->id }}][]" id="attributeSelect_0_{{ $attribute->id }}" class="form-control dynamic-attribute-select">
                                                    <option value="">Select {{ $attribute->name }}</option>
                                                    @foreach($attribute->values as $value)
                                                    @if($value->is_deleted == 0)
                                                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                    @endif
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class='col-md-3 col-xl-3'>
                                        <div class='mb-3'>
                                            <a style="float: right;" href="{{ route('user.add.attributes', $business_card->card_id) }}" class="btn btn-primary">{{ __('Add Attribute') }}</a>
                                        </div>
                                    </div>
                                    <div class='col-md-3 col-xl-3'>
                                        <div class='mb-3'>
                                            <a style="float: right;" href="{{ route('user.add.values', $business_card->card_id) }}" class="btn btn-primary">{{ __('Add Value') }}</a>
                                        </div>
                                    </div>
                                @endif
                                @if (isset($inventory) && $inventory->is_active == 1)
                                <div class='col-md-3 col-xl-3'>
                                    <div class='mb-3'>
                                        <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                        <div class="input-group mb-2">
                                            <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}' value="">
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-3 col-xl-3'>
                                    <div class='mb-3'>
                                        <label class='form-label'><strong>{{ __('Stock Quantity') }}</strong></label>
                                        <div class='input-group mb-2'>
                                            <input type='number' class='form-control stock-input' name='stock_statuses[0]' placeholder='{{ __('Quantity') }}' value="" oninput="updateInventoryField()">
                                            <button type="button" id="addVariantBtn" class="btn btn-primary mx-2">+</button>
                                            <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                                        </div>
                                    </div>
                                </div>
                                @else
                                <div class='col-md-3 col-xl-3'>
                                    <div class='mb-3'>
                                        <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                        <div class="input-group mb-2">
                                            <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}' value="">
                                            <button type="button" id="addVariantBtn" class="btn btn-primary mx-2">+</button>
                                            <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                <div class='col-md-3 col-xl-3' id='variantsTotal' style="display:none">
                                    <div class='mb-3'>
                                        <span id="variantsTotalText">1</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                            
                                                <div class='col-md-6 col-xl-6'>
                                                    <div class='mb-3'>
                                                        <label class='form-label required' for='product_status'><strong>{{ __('Status') }}</strong><span class='text-danger'>*</span></label>
                                                        <select name='product_status[]' id='product_status' class='form-control'>
                                                            {{-- <option value=''>{{ __('Select Status') }}</option> --}}
                                                            <option value='instock'>{{ __('In Stock') }}</option>
                                                            <option value='outstock'>{{ __('Out of Stock') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @if(isset($productvariants) && $productvariants->is_active == 1)
                                            @if (isset($inventory) && $inventory->is_active == 1)
                                            <div class = "col-md-6 col-xl-6">
                                                <div class = "row">
                                                <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                        <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Total Stock Quantity') }}'style = "display:none;">
                                                </div>
                                                <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="stock" value="0"> <!-- Hidden input to send 0 when unchecked -->
                                                        <input class="form-check-input toggle-status" type="checkbox" name="stock" value="1" {{ old('stock') == 1 ? 'checked' : '' }}>
                                                        <label class='form-check-label'><strong>{{ __('Show Inventory') }}</strong></label>
                                                    </div>
                                            </div>
                                            </div>
                                            </div>
                                            @endif
                                            @else
                                            @if (isset($inventory) && $inventory->is_active == 1)
                                            <div class = "col-md-6 col-xl-6">
                                                <div class = "row">
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label' for='inventory'><strong>{{ __('Quantity / Inventory') }}</strong><span class='text-danger'>*</span></label>
                                                    <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Enter Stock Quantity') }}'>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="stock" value="0"> <!-- Hidden input to send 0 when unchecked -->
                                                    <input class="form-check-input toggle-status" type="checkbox" name="stock" value="1" {{ old('stock') == 1 ? 'checked' : '' }}>
                                                    <label class='form-check-label'><strong>{{ __('Show Inventory') }}</strong></label>
                                                </div>
                                        </div>
                                        </div>
                                    </div>
                                            @endif
                                            @endif

                                            
                                         
                            <div id="step-7" class="content col-md-12 col-xl-12">
                                <div class="row">
                                    <div class="col-md-12 col-xl-12 col-sm-12">
                                        <div class="mb-3 bootstrap-tagsinput">
                                        <label class="form-label required"><strong>{{ __('SEO Tags') }}</strong> <small> [After each tag enter Comma ( , )  ]</small></label>
                                            <ul class="tags-input form-control d-table">
                                                <li class="tags-new">
                                                  <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                                </li>
                                            </ul>  
                                            <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords" id="seo_keywords_data"> 
                                            <!-- <input type="text" class="form-control " -->
                                            <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                            <!--required>-->
                                        </div>
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                    <div id = "serviceDetails" class="col-xl-12" style = "display:none">
                        <h5 class="page-title my-3">{{ __('Service Details') }}</h5>
                        <div class='row' id="{{ $i }}">
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='service_status'><strong>{{ __('Status') }}</strong><span class='text-danger'>*</span></label>
                                    <select name='service_status[]' id='product_status' class='form-control'>
                                        {{-- <option value=''>{{ __('Select Status') }}</option> --}}
                                        <option value='Available'>{{ __('Available') }}</option>
                                        <option value='Not Available'>{{ __('Not Available') }}</option>
                                    </select>
                                </div>
                            </div>
                        <div class='col-md-6 col-xl-6'>
                            <div class='mb-3'>
                                <label class='form-label required' for='appointment'><strong>{{__('Book An Appointment') }}</strong><span class="text-danger">*</span></label>
                                <select name='appointment[]' id='appointment' class='form-control' onchange="toggleAppointmentFields()">
                                    <option value=''>{{ __('Do you require Appointment?') }}</option>
                                    <option value='1'>{{ __('Yes') }}</option>
                                    <option value='0'>{{ __('No') }}</option>
                                    <option value='2'>{{ __('Customized Slots') }}</option>
                                </select>
                            </div>
                        </div>
                        <div id="appointmentFields" style="display: none;"  class='col-md-12 col-xl-12'>
                         
                                <div class='row'>
                                    <!-- Dynamic attribute fields -->
                        
                                    {{-- <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class="form-label required"><strong>Date</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='date' class='form-control' name='slot_date[]' placeholder='{{ __('Date') }}' value="">
                                            </div>
                                        </div>
                                    </div> --}}
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slot Duration') }}(Minutes)</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='number' class='form-control' name='slot_duration[]' placeholder='{{ __('Slot Duration') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Break Duration') }}(Minutes)</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='number' class='form-control' name='slot_break[]' placeholder='{{ __('Slot Break') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slots Start') }}</strong></label>
                                            <div class='input-group mb-2'>
                                                <input type='time' class='form-control' name='slot_start[]' placeholder='{{ __('Slots Start') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slots End') }}</strong></label>
                                            <div class='input-group mb-2'>
                                                <input type='time' class='form-control' name='slot_end[]' placeholder='{{ __('Slots End') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                </div>
                            </div>

                            <div id="customizedFields" style="display: none;"  class='col-md-12 col-xl-12'>
                         
                                <div class='row'>
                                    <!-- Dynamic attribute fields -->
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class="form-label required"><strong>Date</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='text' id="multi-date-picker" class='form-control' name='cust_slot_date[]' placeholder='{{ __('Select Dates') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                                    
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slot Duration') }}(Minutes)</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='number' class='form-control' name='cust_slot_duration[]' placeholder='{{ __('Slot Duration') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Break Duration') }}(Minutes)</strong></label>
                                            <div class="input-group mb-2">
                                                <input type='number' class='form-control' name='cust_slot_break[]' placeholder='{{ __('Slot Break') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slots Start') }}</strong></label>
                                            <div class='input-group mb-2'>
                                                <input type='time' class='form-control' name='cust_slot_start[]' placeholder='{{ __('Slots Start') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label'><strong>{{ __('Slots End') }}</strong></label>
                                            <div class='input-group mb-2'>
                                                <input type='time' class='form-control' name='cust_slot_end[]' placeholder='{{ __('Slots End') }}' value="">
                                            </div>
                                        </div>
                                    </div>
                        
                                </div>
                            </div>
                        
                        <div id="step-7" class="content col-md-12 col-xl-12">
                            <div class="row">
                                <div class="col-md-12 col-xl-12 col-sm-12">
                                    <div class="mb-3 bootstrap-tagsinput">
                                    <label class="form-label required"><strong>{{ __('SEO Tags') }}</strong> <small> [After each tag enter Comma ( , )  ]</small></label>
                                        <ul class="tags-input form-control d-table">
                                            <li class="tags-new">
                                              <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                            </li>
                                        </ul>  
                                        <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords" id="seo_keywords_data"> 
                                        <!-- <input type="text" class="form-control " -->
                                        <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                        <!--required>-->
                                    </div>
                                </div>
                            </div> 
                        </div>
                    </div>
                    
                    </div>
                    @else
                    <div class="col-xl-12">
                        <h3 class="page-title my-3">{{ __('Product Details') }}</h3>
                        <?php $i = 0; ?>
                        <div class='row' id="{{ $i }}">
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_status'>
                                        <strong>{{ __('Categories') }}</strong>
                                        <span class="text-danger">*</span> 
                                    </label>
                                    <a style="float: right;" href="{{ route('user.create.category', $business_card->card_id) }}">{{ __('Add Category') }}</a>
                                    <select name='categories[]' id='assign_category' class='form-control' required>
                                        <option value=''>Select Category</option>
                                        @foreach ($categories as $category)
                                            <option value='{{ $category->category_id }}'>{{ __($category->category_name) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_status'><strong>{{ __('Sub Categories') }}</strong><span class = "text-danger">*</span></label>
                                    <a style="float: right;" href="{{ route('user.create.subcategory',$business_card->card_id) }}">{{__('Add Sub Category')}}</a>
                                    <select name='sub_category[]' id='assign_subcategory' class='form-control' required>
                                        <option value=''>Select Sub Category</option>
                                        {{-- <option value='0' >No Sub Category Available</option> --}}
                                        <!--@foreach ($sub_categories as $sub_category)-->
                                        <!--<option value='{{ $sub_category->sub_category_id }}'>{{ __($sub_category->sub_category_name) }}</option>-->
                                        <!--@endforeach-->
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6 d-none'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Product Badge') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='text' class='form-control' value="123" name='badge[]' placeholder='{{ __(' Product Badge') }}...' value="" required>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required'><strong>{{ __('Product Image') }}</strong><span class = "text-danger">*</span></label>
                                    <div class='input-group mb-2' style="justify-content: space-between;align-items: center;">
                                        <input type='hidden' class='image1 media-model form-control'  name='product_image[]' placeholder='{{ __(' Product Image') }}' value="" >
                                        <label class='mb-0'>
                                            <strong><span id="images_count_uploaded"> 0</span> Images has been uploaded.</strong>
                                         </label>
                                        <button class='btn btn-primary btn-md' type='button' onclick="openMedia(1)">{{ __('Choose image') }}</button>
                                    </div>
                                    <small>(Max 3 Images Allowed) (Image should be 800*800)</small></br>
                                    
                                    <small>Image Resize : <a href="https://imageresizer.com/" target="blank"><b>Click Here.</b></a></small>
                                </div>
                                <div id="iamges_preview" class="row">
                                    
                                   
                                 
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class = "row">
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required'><strong>{{ __('Product / Service Name') }}</strong><span class = "text-danger">*</span></label>
                                            <input type='text' class='form-control' name='product_name[]' placeholder='{{ __(' Product / Service Name') }}' value="" required>
                                        </div>
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required'><strong>{{ __('SKU') }}</strong></label>
                                            <input type='text' class='form-control'  pattern="[a-zA-Z0-9]+" name='sku[]' placeholder='{{ __('SKU') }}' value="">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class='col-md-12 col-xl-12'>
                                <div class='mb-3'>
                                  <label class='form-label required'><strong>{{ __('Product Description') }}</strong><span class="text-danger">*</span></label>
                                  <textarea class='form-control' maxlength="512" name='product_subtitle[]' id="product_subtitle"
                                    data-bs-toggle='autosize' placeholder='{{ __('Product Description') }}...' required></textarea>
                                  <div>
                                    <span id="current-title">0</span> / <span id="maximum-title">512</span> {{ __('characters') }}
                                  </div>
                                </div>
                              </div>
                              
                              @php
                              $plugin_variants = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Product Variants' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
    if(isset($plugin_variants))
    {
        $productvariants = App\Models\Extension::where('card_id', $store_id)
                                                   ->where('plugin_id', $plugin_variants->id)
                                                   ->where('is_deleted', 0)
                                                   ->first();
    }
                               
                               $plan = DB::table('users')
                                         ->where('user_id', Auth::user()->user_id)
                                         ->where('status', 1)
                                         ->first();
                               $plan_details = DB::table('plans')
                                                 ->where('plan_id', $plan->plan_id)
                                                 ->first();
                                                 $plugin_inventory = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Inventory Management' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
    if(isset($plugin_inventory))
    {
        $inventory = App\Models\Extension::where('plugin_id',$plugin_inventory->id)->where('card_id',$store_id)->where('is_deleted',0)->first();
    }
             $maxVariants = $plan_details->no_of_variants; 
             @endphp
                          
                          @if (isset($productvariants) && $productvariants->is_active == 1)
                          {{-- <div class='col-md-6 col-xl-6'>
                            <label class='form-label'><strong>{{ __('Product Variants') }}</strong></label>
                           
                        </div>
                          <div class = "col-md-6 col-xl-6">
                            <div class = "row">
                                <div class = "col-md-6 col-xl-6">
                                  
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="variants_check" id="variants-no" value="No" onchange="toggleVariantFields()">
                                            <label class="form-check-label" for="icon-jpg">No</label>
                                        </div>
                                      
                                    
                                </div>
                                <div class = "col-md-6 col-xl-6">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="variants_check" id="variants-yes" value="Yes" onchange="toggleVariantFields()">
                                        <label class="form-check-label" for="icon-video">Yes</label>
                                    </div>
                                
                            </div>
                            </div>
                        </div> --}}
                        <br><br>
                          <div class='col-md-12 col-xl-12' id="variant-fields-container">
                                  <div class='row variant-field'>
                                      <!-- Dynamic attribute fields -->
                                      @if(isset($attributes) && $attributes->count() > 0)
                                          @foreach($attributes as $index => $attribute)
                                              <div class='col-md-3 col-xl-3'>
                                                  <div class='mb-3'>
                                                      <label class="form-label required"><strong>{{ $attribute->name }}</strong></label>
                                                      <a style="float: right;" href="{{ route('user.add.attributes', $business_card->card_id) }}">{{ __('Add Attribute') }}</a>
                                                      <select name="attributes[0][{{ $attribute->id }}][]" id="attributeSelect_0_{{ $attribute->id }}" class="form-control dynamic-attribute-select">
                                                          <option value="">Select {{ $attribute->name }}</option>
                                                          @foreach($attribute->values as $value)
                                                          @if($value->is_deleted == 0)
                                                          <option value="{{ $value->id }}">{{ $value->name }}</option>
                                                      @endif
                                                          @endforeach
                                                      </select>
                                                  </div>
                                              </div>
                                          @endforeach
                                      @else
                                          <div class='col-md-3 col-xl-3'>
                                              <div class='mb-3'>
                                                  <a style="float: right;" href="{{ route('user.add.attributes', $business_card->card_id) }}" class="btn btn-primary">{{ __('Add Attribute') }}</a>
                                              </div>
                                          </div>
                                          <div class='col-md-3 col-xl-3'>
                                              <div class='mb-3'>
                                                  <a style="float: right;" href="{{ route('user.add.values', $business_card->card_id) }}" class="btn btn-primary">{{ __('Add Value') }}</a>
                                              </div>
                                          </div>
                                      @endif
                                      @if (isset($inventory) && $inventory->is_active == 1)
                                      <div class='col-md-3 col-xl-3'>
                                          <div class='mb-3'>
                                              <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                              <div class="input-group mb-2">
                                                  <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}' value="">
                                              </div>
                                          </div>
                                      </div>
                                      <div class='col-md-3 col-xl-3'>
                                          <div class='mb-3'>
                                              <label class='form-label'><strong>{{ __('Stock Quantity') }}</strong></label>
                                              <div class='input-group mb-2'>
                                                  <input type='number' class='form-control stock-input' name='stock_statuses[0]' placeholder='{{ __('Quantity') }}' value="" oninput="updateInventoryField()">
                                                  <button type="button" id="addVariantBtn" class="btn btn-primary mx-2">+</button>
                                                  <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                                              </div>
                                          </div>
                                      </div>
                                      @else
                                      <div class='col-md-3 col-xl-3'>
                                          <div class='mb-3'>
                                              <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                              <div class="input-group mb-2">
                                                  <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}' value="">
                                                  <button type="button" id="addVariantBtn" class="btn btn-primary mx-2">+</button>
                                                  <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                                              </div>
                                          </div>
                                      </div>
                                      @endif
                                      <div class='col-md-3 col-xl-3' id='variantsTotal' style = "display:none">
                                          <div class='mb-3'>
                                              <span id="variantsTotalText">1</span>
                                          </div>
                                      </div>
                                  </div>
                              
                          </div>
                          @endif
                              <div class='col-md-6 col-xl-6'>
                                <div class='row'>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Product Documentation Title') }}</strong></label>
                                        <input type='text' class='form-control' name='vedio_title[]' placeholder='{{ __('Product Documentation Title') }}' value="">
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Product Documentation Icon') }}</strong></label>
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-jpg" value="jpg">
                                                <label class="form-check-label" for="icon-jpg">
                                                    <i class="fa fa-image"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-video" value="video">
                                                <label class="form-check-label" for="icon-video">
                                                    <i class="fa fa-play"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-document" value="document">
                                                <label class="form-check-label" for="icon-document">
                                                    <i class="fa fa-file"></i>
                                                </label>
                                            </div>
                                        </div>
                                    </div>   
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Product Documentation') }}</strong></label>
                                    <input type='text' class='form-control' name='vedio_link[]' value="" placeholder='{{ __(' Product Documentation Link') }}'>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='row'>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label required'><strong>{{ __('No of units') }}</strong><span class = "text-danger">*</span></label>
                                        <input type='number' class='form-control' name='no_of_units[]'  min='1' placeholder='{{ __(' No of units') }}...' value="1" min='1' step='1' required>
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <label class='form-label required'><strong>{{ __('Unit') }}</strong></label>
                                        <select name="units[]" class="form-control" >
                                            <option value="">Select Unit</option>
                                            @foreach($units as $units_data)
                                                <option value="{{$units_data->unit_shortname}}" >{{$units_data->unit_name}}</option>
                                            @endforeach
                                        </select>
                                    </div>   
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_price_status'><strong>{{__('Price Show/Hide') }}</strong><span class = "text-danger">*</span></label>
                                    <select name='product_price_status[]' id='product_price_status' class='form-control'>
                                        <option value='1' > {{ __('Show Price') }}</option>
                                        <option value='0' >{{ __('Hide Price') }}</option>
                                        
                                    </select>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'><label class='form-label'><strong>{{ __('Regular Price') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='number' class='form-control' name='regular_price[]' min='1' placeholder='{{ __(' Regular Price') }}...' value="" min='1' step='.001'>
                                </div>
                            </div>
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label'><strong>{{ __('Sales Price') }}</strong><span class = "text-danger">*</span></label>
                                    <input type='number' class='form-control' name='sales_price[]' min='1' step='.001' value="" placeholder='{{ __(' Sales Price') }}...'>
                                </div>
                            </div>  
                            <div class='col-md-6 col-xl-6'>
                                <div class='mb-3'>
                                    <label class='form-label required' for='product_status'><strong>{{ __('Status') }}</strong><span class='text-danger'>*</span></label>
                                    <select name='product_status[]' id='product_status' class='form-control'>
                                        {{-- <option value=''>{{ __('Select Status') }}</option> --}}
                                        <option value='instock'>{{ __('In Stock') }}</option>
                                        <option value='outstock'>{{ __('Out of Stock') }}</option>
                                    </select>
                                </div>
                            </div>     
                            @if(isset($productvariants) && $productvariants->is_active == 1)
                                            @if (isset($inventory) && $inventory->is_active == 1)
                                            <div class = "col-md-6 col-xl-6">
                                                <div class = "row">
                                                <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                        <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Total Stock Quantity') }}'style = "display:none;">
                                                </div>
                                                <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                    <div class="form-check form-switch">
                                                        <input type="hidden" name="stock" value="0"> <!-- Hidden input to send 0 when unchecked -->
                                                        <input class="form-check-input toggle-status" type="checkbox" name="stock" value="1" {{ old('stock') == 1 ? 'checked' : '' }}>
                                                        <label class='form-check-label'><strong>{{ __('Show Inventory') }}</strong></label>
                                                    </div>
                                            </div>
                                            </div>
                                            </div>
                                            @endif
                                            @else
                                            @if (isset($inventory) && $inventory->is_active == 1)
                                            <div class = "col-md-6 col-xl-6">
                                                <div class = "row">
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label' for='inventory'><strong>{{ __('Quantity / Inventory') }}</strong><span class='text-danger'>*</span></label>
                                                    <input type='number' class='form-control' id='inventory' name='inventory[]' placeholder='{{ __('Enter Stock Quantity') }}'>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6' id='inventory_field'>
                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="stock" value="0"> <!-- Hidden input to send 0 when unchecked -->
                                                    <input class="form-check-input toggle-status" type="checkbox" name="stock" value="1" {{ old('stock') == 1 ? 'checked' : '' }}>
                                                    <label class='form-check-label'><strong>{{ __('Show Inventory') }}</strong></label>
                                                </div>
                                        </div>
                                        </div>
                                    </div>
                                            @endif
                                            @endif   
                                            <div id="step-7" class="content col-md-12 col-xl-12">
                                                <div class="row">
                                                    <div class="col-md-12 col-xl-12 col-sm-12">
                                                        <div class="mb-3 bootstrap-tagsinput">
                                                        <label class="form-label required"><strong>{{ __('SEO Tags') }}</strong> <small> [After each tag enter Comma ( , )  ]</small></label>
                                                            <ul class="tags-input form-control d-table">
                                                                <li class="tags-new">
                                                                  <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                                                </li>
                                                            </ul>  
                                                            <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords" id="seo_keywords_data"> 
                                                            <!-- <input type="text" class="form-control " -->
                                                            <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                                            <!--required>-->
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>
                                               
                            </div>
                        </div>
                    @endif
                    <div id="more-products" class="row"></div>

                    <div class="col-lg-12 mt-2">
                       <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>  
        
        

<div class="modal fade" id="openMediaModel" tabindex="-1" role="dialog" aria-labelledby="grid-modal" aria-hidden="true">

     <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Media Library')}}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body scroll-box">
                <div class="row row-cards" id="captions">
                    {{-- Upload multiple images --}}
                    <div class="col-sm-12 col-lg-12 mb-4">
                        <form action="{{ route('user.multiple') }}" class="dropzone" id="dropzone"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="dz-message">
                                {{ __('Drag and Drop Single/Multiple Files Here') }} <br>
                            </div>
                             <input type="hidden" value="{{$business_card->card_id}}" name="card_url">
                            <input type="hidden" value="1" name="image_type">
                        </form>
                    </div>
                    <div  class="col-sm-12 col-lg-12 mb-4">
                        <div class="row" id="modalImages"> 
                    @if (!empty($media) && $media->count())
                    @foreach ($media as $gallery)
                    <div class="col-6 col-sm-4">
                        <label class="form-imagecheck mb-2">
                            <input name="form-imagecheck" type="checkbox" value="{{ $gallery->media_url }}" id="{{ $gallery->id }}" onclick="checkforselected({{ $gallery->id }})" class="form-imagecheck-input media_image">
                          
                          <span class="form-imagecheck-figure">
                            <img src="{{asset($gallery->media_url) }}" class="form-imagecheck-image" >
                          </span>
                        </label>
                    </div>
                    @endforeach
                    @else
                    <div class="empty">
                        <div class="empty-img"><img src="{{asset('backend/img/undraw_printing_invoices_5r4r.svg') }}"
                                height="128" alt="">
                        </div>
                        <p class="empty-title">{{ __('No media found') }}</p>
                        <p class="empty-subtitle text-muted">
                            {{ __('Try adjusting your add to find what you are looking for.') }}
                        </p>
                    </div>
                    @endif
                    </div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{ __('Select') }}</button>
            </div>
        </div>
    </div>
</div>
<style>
    .cke_notification_warning {
  display: none !important; /* Force hide the warning notification */
}
    </style>
@section('script')
<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#multi-date-picker", {
            mode: "multiple",
            dateFormat: "d-m-Y", // Format the selected dates
            minDate: "today" // Optional: to restrict past dates
        });
    });
</script>
<script>
    function toggleVariantFields() {
        const variantFieldsContainer = document.getElementById('variant-fields-container');
        const yesRadioButton = document.getElementById('variants-yes');
        const yesinventoryButton = document.getElementById('variant_inventory_yes');
        const noinventoryButton = document.getElementById('variant_inventory_no');


        // Show the fields if "Yes" is selected
        if (yesRadioButton.checked) {
            variantFieldsContainer.style.display = 'block';
            noinventoryButton.style.display = 'block';
            yesinventoryButton.style.display = 'none';
        } else {
            variantFieldsContainer.style.display = 'none';
            noinventoryButton.style.display = 'none';
            yesinventoryButton.style.display = 'block';
        }
    }
</script>
<script>
    $(document).ready(function() {
    // When "Add Product" is clicked
    $('#productBtn').click(function() {
        // Hide the service section (if it was previously shown)
        $('#serviceDetails').hide();
        // Show the product details section
        $('#productDetails').show();
    });

    // When "Add Service" is clicked
    $('#serviceBtn').click(function() {
        // Hide the product section (if it was previously shown)
        $('#productDetails').hide();
        // Show the service details section
        $('#serviceDetails').show();
    });
});

    </script>
<script>
    function toggleAppointmentFields() {
        var appointmentStatus = document.getElementById('appointment').value;
        var appointmentFields = document.getElementById('appointmentFields');
        var customizedFields = document.getElementById('customizedFields');
    
        // Show the appointment fields if 'Yes' is selected, hide if 'No'
        if (appointmentStatus == '1') {
            appointmentFields.style.display = 'block';
            customizedFields.style.display = 'none';
        }
        else if(appointmentStatus == '2')
        {
            appointmentFields.style.display = 'none';
            customizedFields.style.display = 'block';
        } 
        else 
        {
            appointmentFields.style.display = 'none';
            customizedFields.style.display = 'none';
        }
    }
    </script>
{{-- <script>
    const maxVariants = {{ $maxVariants }}; // Maximum number of variants allowed

    $(document).ready(function() {
        // Attach click event to Add Variant button
        $('#addVariantBtn').on('click', function() {
            addVariant(); // Call addVariant function
            updateVariantsCount(); // Call updateVariantsCount function after adding a variant
        });

        // Attach click event to Remove Variant buttons (dynamically created variants)
        $(document).on('click', '.remove-variant-btn', function() {
            removeVariant(this); // Call removeVariant function
            updateVariantsCount(); // Call updateVariantsCount function after removing a variant
        });

        // Initialize counts when the page loads
        updateVariantsCount();
    });

    function updateInventoryField() {
        let stockInputs = document.querySelectorAll('.stock-input');
        let totalStock = 0;

        // Calculate the total stock quantity by summing all stock input values
        stockInputs.forEach(function(input) {
            let value = parseInt(input.value, 10);
            if (!isNaN(value)) {
                totalStock += value;
            }
        });

        // Update the inventory field with the total stock
        let inventoryInput = document.getElementById('inventory');
        inventoryInput.value = totalStock;
    }

    function updateVariantsCount() {
        let container = document.getElementById('variant-fields-container');
        let currentVariants = container.querySelectorAll('.variant-field');
        let variantsTotalElement = document.getElementById('variantsTotalText');

        // Get the number of current variant fields
        let variantsCount = currentVariants.length;

        // Update the span with the current number of variants
        variantsTotalElement.textContent = variantsCount;

        let addVariantBtn = document.getElementById('addVariantBtn');
        // Show or hide the Add Variant button based on the current count and maxVariants
        addVariantBtn.style.display = variantsCount >= maxVariants ? 'none' : 'inline-block';
    }

    function addVariant() {
        let container = document.getElementById('variant-fields-container');
        let variantField = document.querySelector('.variant-field');
        let newField = variantField.cloneNode(true);
        let currentIndex = container.querySelectorAll('.variant-field').length;

        // Update names and IDs for new fields to reflect their new index
        newField.querySelectorAll('select, input').forEach(function(element) {
            if (element.name) {
                element.name = element.name.replace(/\[0\]/, '[' + currentIndex + ']');
            }
            if (element.id) {
                element.id = element.id.replace(/_0_/, '_' + currentIndex + '_');
            }
            element.value = ''; // Clear values for new fields
        });

        container.appendChild(newField);
        updateInventoryField();
    }

    function removeVariant(button) {
        let container = document.getElementById('variant-fields-container');
        let fields = container.querySelectorAll('.variant-field');

        // Only remove if more than one variant field exists
        if (fields.length > 1) {
            let variantField = button.closest('.variant-field');
            // Remove the variant field from the DOM
            variantField.remove();

            // Recalculate the inventory and variants count after removing the variant
            updateInventoryField();
            updateVariantsCount();
        }
    }
</script> --}}
<script>
    // Function to toggle inventory field visibility
    function toggleInventoryField() {
        var status = document.getElementById('product_status').value;
        var inventoryField = document.getElementById('inventory_field');
        
        if (status === 'instock') {
            inventoryField.style.display = 'block';
        } else {
            inventoryField.style.display = 'none';
        }
    }

    // Attach event listener to product status select
    document.getElementById('product_status').addEventListener('change', toggleInventoryField);

    // Call the function initially to set the correct display state based on the default value
    toggleInventoryField();
</script>
<script>
   CKEDITOR.replace('product_subtitle', {
//   removePlugins: 'notification,notificationaggregator', // This will remove notifications
//   disableNativeSpellChecker: false, // Just a safeguard for text handling
  toolbar: [
    { name: 'basicstyles', items: ['Bold', 'Italic'] },
    { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
    { name: 'links', items: ['Link'] },
    { name: 'undo', items: ['Undo', 'Redo'] }
  ],
  removeButtons: 'Image,Video',
  on: {
    instanceReady: function (ev) {
      var editor = ev.editor;
      editor.on('contentDom', function () {
        editor.document.on('keyup', function () {
          var textLength = editor.getData().length;
          document.getElementById('current-title').textContent = textLength;
        });
      });
    }
  }
});
CKEDITOR.on('instanceReady', function(event) {
  var warningElement = document.querySelector('.cke_notification_warning');
  if (warningElement) {
    warningElement.remove(); // Remove the notification from the DOM
  }
});
  </script>

<script>
    CKEDITOR.replace('service_subtitle', {
 //   removePlugins: 'notification,notificationaggregator', // This will remove notifications
 //   disableNativeSpellChecker: false, // Just a safeguard for text handling
   toolbar: [
     { name: 'basicstyles', items: ['Bold', 'Italic'] },
     { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
     { name: 'links', items: ['Link'] },
     { name: 'undo', items: ['Undo', 'Redo'] }
   ],
   removeButtons: 'Image,Video',
   on: {
     instanceReady: function (ev) {
       var editor = ev.editor;
       editor.on('contentDom', function () {
         editor.document.on('keyup', function () {
           var textLength = editor.getData().length;
           document.getElementById('current-title').textContent = textLength;
         });
       });
     }
   }
 });
 CKEDITOR.on('instanceReady', function(event) {
   var warningElement = document.querySelector('.cke_notification_warning');
   if (warningElement) {
     warningElement.remove(); // Remove the notification from the DOM
   }
 });
   </script>
  
{{-- <script>
    document.addEventListener('DOMContentLoaded', function () {
        const textarea = document.getElementById('product_subtitle_1');
        const currentTitle = document.getElementById('current-title');
        const maximumTitle = document.getElementById('maximum-title');
        const maxLength = 512;
    
        function updateCount() {
            const currentLength = textarea.value.length;
            currentTitle.textContent = currentLength;
            // Optionally add logic to change color or style based on length
        }
    
        // Initialize count
        updateCount();
    
        // Add event listener to update count on input
        textarea.addEventListener('input', updateCount);
    
        // Initialize ClassicEditor
        ClassicEditor
            .create(textarea)
            .then(editor => {
                // The editor instance is available here
                console.log('Editor initialized', editor);
            })
            .catch(error => {
                console.error('Error initializing ClassicEditor:', error);
            });
    });
    </script> --}}
<script>
//     let variantIndex = 1;
//     const maxVariants = {{ $maxVariants }}; // Maximum number of variants allowed

// $(document).ready(function() {
//     // Attach click event to Add Variant button
//     $('#addVariantBtn').on('click', function() {
//         // addVariant(); // Call addVariant function
//         updateVariantsCount(); // Call updateVariantsCount function after adding a variant
//     });

//     // Attach click event to Remove Variant buttons (dynamically created variants)
//     $(document).on('click', '.remove-variant-btn', function() {
//         removeVariant(this); // Call removeVariant function
//         updateVariantsCount(); // Call updateVariantsCount function after removing a variant
//     });

//     // Initialize counts when the page loads
//     updateVariantsCount();
// });
// function updateInventoryField() {
//         let stockInputs = document.querySelectorAll('.stock-input');
//         let totalStock = 0;

//         // Calculate the total stock quantity by summing all stock input values
//         stockInputs.forEach(function(input) {
//             let value = parseInt(input.value, 10);
//             if (!isNaN(value)) {
//                 totalStock += value;
//             }
//         });

//         // Update the inventory field with the total stock
//         let inventoryInput = document.getElementById('inventory');
//         inventoryInput.value = totalStock;
//     }
// function addVariant() {
//     const variantFieldsContainer = document.getElementById('variant-fields-container');
//     const newVariantRow = document.createElement('div');
//     let container = document.getElementById('variant-fields-container');
//         let variantField = document.querySelector('.variant-field');
//         let newField = variantField.cloneNode(true);
//         let currentIndex = container.querySelectorAll('.variant-field').length;

//     newVariantRow.classList.add('row', 'variant-field');
    
//     newVariantRow.innerHTML = `
//         @foreach($attributes as $index => $attribute)
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class="form-label required">{{ $attribute->name }}</label>
//                 <select name="attributes[${variantIndex}][{{ $attribute->id }}][]" id="attributeSelect_${variantIndex}_{{ $attribute->id }}" class="form-control dynamic-attribute-select" required>
//                     <option value = "">Select {{$attribute->name}}</option>
//                     @foreach($attribute->values as $value)
//                         <option value="{{ $value->id }}">{{ $value->name }}</option>
//                     @endforeach
//                 </select>
//             </div>
//         </div>
//         @endforeach
        
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class='form-label'>{{ __('Price') }}</label>
//                 <div class="input-group mb-2">
//                     <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="" required>
//                 </div>
//             </div>
//         </div>
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class='form-label'>{{ __('Stock Quantity') }}</label>
//                 <div class='input-group mb-2'>
//                     <input type='number' class='form-control' name='stock_statuses[${variantIndex}]' placeholder='{{ __('quantity') }}' value="" required>
//                     <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
//                     <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
//                 </div>
//             </div>
//         </div>
//     `;

//     variantFieldsContainer.appendChild(newVariantRow);
//     variantIndex++;
   
//         // Update names and IDs for new fields to reflect their new index
//         newField.querySelectorAll('select, input').forEach(function(element) {
//             if (element.name) {
//                 element.name = element.name.replace(/\[0\]/, '[' + currentIndex + ']');
//             }
//             if (element.id) {
//                 element.id = element.id.replace(/_0_/, '_' + currentIndex + '_');
//             }
//             element.value = ''; // Clear values for new fields
//         });

//         container.appendChild(newField);
//         updateInventoryField();
//     updateVariantsCount();
// }

// function removeVariant(button) {
//     const variantRow = button.closest('.variant-field');
//     variantRow.remove();
//     let fields = container.querySelectorAll('.variant-field');

//         // Only remove if more than one variant field exists
//         if (fields.length > 1) {
//             let variantField = button.closest('.variant-field');
//             // Remove the variant field from the DOM
//             variantField.remove();

//             // Recalculate the inventory and variants count after removing the variant
//             updateInventoryField();
//             updateVariantsCount();
//         }
// }
let variantIndex = 1;
const maxVariants = {{ $maxVariants }}; // Maximum number of variants allowed

$(document).ready(function () {
    // Attach event delegation to dynamically handle Add and Remove buttons
    $(document).on('click', '#addVariantBtn', function () {
        // addVariant(); // Call addVariant function
        updateVariantsCount(); // Update the variant count after adding a variant
    });

    $(document).on('click', '.remove-variant-btn', function () {
        removeVariant(this); // Remove the variant
        // updateVariantsCount(); // Update the variant count after removing a variant
    updateTotalStockQuantity();

    });

    $(document).on('input', '.stock-input', function () {
        updateTotalStockQuantity();
    });

    // Initialize counts when the page loads
    // updateVariantsCount();
});

function addVariant() {
    const variantFieldsContainer = document.getElementById('variant-fields-container');
    const newVariantRow = document.createElement('div');
    newVariantRow.classList.add('row', 'variant-field');

    // Use the existing attributes and prices to generate new variant fields dynamically
    newVariantRow.innerHTML = `
        @foreach($attributes as $index => $attribute)
        <div class='col-md-3 col-xl-3'>
            <div class='mb-3'>
                <label class="form-label required">{{ $attribute->name }}</label>
                <select name="attributes[${variantIndex}][{{ $attribute->id }}][]" id="attributeSelect_${variantIndex}_{{ $attribute->id }}" class="form-control dynamic-attribute-select" required>
                    <option value="">Select {{ $attribute->name }}</option>
                    @foreach($attribute->values as $value)
                        <option value="{{ $value->id }}">{{ $value->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endforeach
         @if (isset($inventory) && $inventory->is_active == 1)
        <div class='col-md-3 col-xl-3'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Price') }}</label>
                <div class="input-group mb-2">
                    <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="" required>
                </div>
            </div>
        </div>
        <div class='col-md-3 col-xl-3'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Stock Quantity') }}</label>
                <div class='input-group mb-2'>
                    <input type='number' class='form-control stock-input' name='stock_statuses[${variantIndex}]' placeholder='{{ __('Quantity') }}' value="" required>
                    <button type="button" id = "addVariantBtn" class="btn btn-primary mx-2 add-variant-btn">+</button>
                    <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                </div>
            </div>
        </div>
        @else
         <div class='col-md-3 col-xl-3'>
            <div class='mb-3'>
                <label class='form-label'>{{ __('Price') }}</label>
                <div class="input-group mb-2">
                    <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="" required>
                    <button type="button" id = "addVariantBtn" class="btn btn-primary mx-2 add-variant-btn">+</button>
                    <button type="button" class="btn btn-danger remove-variant-btn" onclick="removeVariant(this)">-</button>
                </div>
            </div>
        </div>
        @endif
    `;

    // Append the newly created variant row
    variantFieldsContainer.appendChild(newVariantRow);

    // Increment the index for the next variant
    variantIndex++;

    updateVariantsCount(); // Update variant count after adding a variant
}

function removeVariant(button) {
    const variantRow = button.closest('.variant-field');
    variantRow.remove(); // Remove the clicked variant field
    updateVariantsCount(); // Recalculate variant count after removing a variant

}

function updateVariantsCount() {
    const variantFields = document.querySelectorAll('.variant-field');
    const variantsTotalText = document.getElementById('variantsTotalText');
    variantsTotalText.innerText = variantFields.length; // Update total variants displayed
const totalVariants = variantFields.length;
    // let addVariantBtn = document.getElementById('addVariantBtn');
    if(totalVariants >= maxVariants)
{
    alert('you have reached limit')
}
else
{
    addVariant();
}
    // addVariantBtn.style.display = totalVariants > maxVariants ? 'none' : 'inline-block';
}

function updateTotalStockQuantity() {
    let totalQuantity = 0;
    $('.stock-input').each(function () {
        let quantity = parseInt($(this).val()) || 0;
        totalQuantity += quantity;
    });
    $('#inventory').val(totalQuantity);
}


// let variantIndex = 1;
// const maxVariants = {{ $maxVariants }}; // Maximum number of variants allowed

// $(document).ready(function () {
//     // Add variant button handler
//     $(document).on('click', '#addVariantBtn', function () {
//         if (variantIndex < maxVariants) {
//             addVariant();
//             updateVariantsCount();
//         } else {
//             alert('You have reached the limit of variants allowed.');
//         }
//     });

//     // Remove variant button handler
//     $(document).on('click', '.remove-variant-btn', function () {
//         removeVariant(this);
//         updateVariantsCount();
        
//     });

//     // Update total quantity when the stock quantity input changes
//     $(document).on('input', '.stock-input', function () {
//         updateTotalStockQuantity();
//     });

//     updateVariantsCount(); // Initialize the counts when the page loads
// });

// function addVariant() {
//     const variantFieldsContainer = document.getElementById('variant-fields-container');
//     const newVariantRow = document.createElement('div');
//     newVariantRow.classList.add('row', 'variant-field');

//     newVariantRow.innerHTML = `
//         @foreach($attributes as $index => $attribute)
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class="form-label required">{{ $attribute->name }}</label>
//                 <select name="attributes[${variantIndex}][{{ $attribute->id }}][]" id="attributeSelect_${variantIndex}_{{ $attribute->id }}" class="form-control dynamic-attribute-select" required>
//                     <option value="">Select {{ $attribute->name }}</option>
//                     @foreach($attribute->values as $value)
//                         <option value="{{ $value->id }}">{{ $value->name }}</option>
//                     @endforeach
//                 </select>
//             </div>
//         </div>
//         @endforeach
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class='form-label'>{{ __('Price') }}</label>
//                 <div class="input-group mb-2">
//                     <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="" required>
//                 </div>
//             </div>
//         </div>
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class='form-label'>{{ __('Stock Quantity') }}</label>
//                 <div class='input-group mb-2'>
//                     <input type='number' class='form-control stock-input' name='stock_statuses[${variantIndex}]' placeholder='{{ __('Quantity') }}' value="" required>
//                     <button type="button" class="btn btn-primary mx-2 add-variant-btn">+</button>
//                     <button type="button" class="btn btn-danger remove-variant-btn">-</button>
//                 </div>
//             </div>
//         </div>
//     `;

//     variantFieldsContainer.appendChild(newVariantRow);
//     variantIndex++;
// }

// function removeVariant(button) {
//     const variantRow = button.closest('.variant-field');
//     variantRow.remove();
//     updateTotalStockQuantity();
// }

// function updateVariantsCount() {
//     const variantFields = document.querySelectorAll('.variant-field');
//     const variantsTotalText = document.getElementById('variantsTotalText');
//     variantsTotalText.innerText = variantFields.length;
//     const totalVariants = variantFields.length;
//     // let addVariantBtn = document.getElementById('addVariantBtn');
//     if(totalVariants >= maxVariants)
// {
//     alert('you have reached limit')
// }
// else
// {
//     addVariant();
// }
// }

// function updateTotalStockQuantity() {
//     let totalQuantity = 0;
//     $('.stock-input').each(function () {
//         let quantity = parseInt($(this).val()) || 0;
//         totalQuantity += quantity;
//     });
//     $('#inventory').val(totalQuantity);
// }


</script>
<script>
    $(document).ready(function() {
        $('.dynamic-attribute-select').on('change', function() {
            var index = $(this).attr('id').split('_').pop(); // Get the index from the ID
            var selectedValue = $(this).val();
            
            // Update the hidden input field with the selected attribute ID
            $('#hiddenAttributeId_' + index).val(selectedValue);
        });
    });
</script>
<script>
    
    $(document).ready(function() {
            // URL for the named route to fetch attributes
            var getAttributesUrl = '{{ route('user.getAttributes') }}';

            $('#create-variations-btn').on('click', function() {
                // Perform AJAX request to fetch attributes
                $.ajax({
                    url: getAttributesUrl,
                    method: 'GET',
                    success: function(data) {
                        if (data.error) {
                            console.error('Error fetching attributes:', data.error);
                            return;
                        }

                        const attributes = data.attributes; // Attributes from the response
                        console.log(attributes);

                        // Display attribute dropdowns
                        const attributesSection = $('#attributes-section');
                        attributesSection.empty(); // Clear previous content

                        const attributeSelects = {};

                        for (const [attributeName, values] of Object.entries(attributes)) {
                            // Create a select field for each attribute value
                            values.forEach((value, index) => {
                                const selectId = `${attributeName}-${index}`;
                                const dropdownHtml = `
                                    <div class="attribute-dropdown">
                                        <label for="${selectId}">${attributeName} ${index + 1}</label>
                                        <select name="${attributeName}[]" id="${selectId}" class="attribute-select">
                                            <option value="${value}">${value}</option>
                                        </select>
                                    </div>
                                `;
                                attributesSection.append(dropdownHtml);

                                // Store the select in an array for processing later
                                if (!attributeSelects[attributeName]) {
                                    attributeSelects[attributeName] = [];
                                }
                                attributeSelects[attributeName].push(`#${selectId}`);
                            });
                        }

                        // Set up change event to generate combinations when selections change
                        $('.attribute-select').on('change', function() {
                            generateCombinations(attributeSelects);
                        });

                        // Initial combinations display
                        generateCombinations(attributeSelects);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error fetching attributes:', error);
                    }
                });
            });

            function generateCombinations(attributeSelects) {
                const selectedValues = {};
                for (const [attributeName, selectors] of Object.entries(attributeSelects)) {
                    selectedValues[attributeName] = selectors.map(selector => $(selector).val()).filter(value => value);
                }

                // Generate all possible combinations
                const combinations = generateCombinationsForAttributes(selectedValues);

                // Display combinations
                const variationsSection = $('#variations-section');
                variationsSection.empty(); // Clear previous content

                combinations.forEach((combination, index) => {
                    const combinationText = Object.entries(combination).map(([key, value]) => `${key}: ${value}`).join(', ');

                    const variationHtml = `
                        <div class="variation">
                            <label for="variation-${index}">${combinationText}</label>
                            <input type="hidden" name="combinations[${index}][attributes]" value='${JSON.stringify(combination)}'>
                            <input type="text" name="combinations[${index}][price]" placeholder="Enter price" class="price-input">
                            <button type="button" class="toggle-enable" data-index="${index}">Disable</button>
                        </div>
                    `;

                    variationsSection.append(variationHtml);
                });

                // Set up click event for toggle buttons
                $('.toggle-enable').on('click', function() {
                    const index = $(this).data('index');
                    const priceInput = $(`input[name="combinations[${index}][price]"]`);
                    if (priceInput.prop('disabled')) {
                        priceInput.prop('disabled', false);
                        $(this).text('Disable');
                    } else {
                        priceInput.prop('disabled', true);
                        $(this).text('Enable');
                    }
                });
            }

            function generateCombinationsForAttributes(selectedValues) {
                let result = [[]];

                for (const [attributeName, values] of Object.entries(selectedValues)) {
                    result = result.flatMap(existingCombination => 
                        values.map(value => 
                            Object.assign({}, existingCombination, { [attributeName]: value })
                        )
                    );
                }

                return result;
            }
        });
    </script>
{{-- <script>
   function addAttribute(button) {
    var container = document.getElementById('variant-fields-container');
    var currentField = button.closest('.variant-field');
    
    // Clone the current variant field
    var newField = currentField.cloneNode(true);
    
    // Reset the values of the cloned inputs
    newField.querySelectorAll('input, select').forEach(function(element) {
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0;
        } else if (element.tagName === 'INPUT') {
            element.value = '';
        }
    });
    
    // Show the remove button in the new field
    newField.querySelector('.btn-danger').style.display = 'inline-block';
    
    // Add event listener for the new remove button
    newField.querySelector('.btn-danger').addEventListener('click', function() {
        removeAttribute(this);
    });
    
    // Append the new field to the container
    container.appendChild(newField);
}

function removeAttribute(button) {
    var currentField = button.closest('.variant-field');
    
    // Only remove the field if there's more than one present
    var variantFields = document.querySelectorAll('.variant-field');
    if (variantFields.length > 1) {
        currentField.remove();
    }
}

// Initialize event listeners for the existing minus buttons
document.querySelectorAll('.btn-danger').forEach(function(button) {
    button.addEventListener('click', function() {
        removeAttribute(this);
    });
});



function removeAttribute(button) {
    var currentField = button.closest('.variant-field');
    
    // Only remove the field if there's more than one present
    var variantFields = document.querySelectorAll('.variant-field');
    if (variantFields.length > 1) {
        currentField.remove();
    }
}

    </script> --}}
    <script>
       function updateValueId(index) {
    const selectElement = document.getElementById(`attributeSelect_${index}`);
    const hiddenInput = document.getElementById(`valueSelect_${index}`);
    
    if (selectElement && hiddenInput) {
        hiddenInput.value = selectElement.options[selectElement.selectedIndex].value;
    }
}

function addVariant() {
    const container = document.getElementById('variant-fields-container');
    const variantField = container.querySelector('.variant-field').cloneNode(true);
    
    // Reset the values of the cloned fields
    variantField.querySelectorAll('input, select').forEach(input => {
        input.value = '';
        if (input.type === 'number') {
            input.disabled = false; // Enable the price input initially
        }
    });

    // Ensure that the remove button is visible in the new row
    variantField.querySelector('.btn-danger').style.display = 'inline-block';

    // Update indices in the cloned fields to avoid conflicts
    const fields = variantField.querySelectorAll('[id], [name]');
    fields.forEach(field => {
        const name = field.getAttribute('name');
        const id = field.getAttribute('id');

        if (name) {
            field.setAttribute('name', name.replace(/\[\d+\]/, `[${container.children.length}]`));
        }

        if (id) {
            field.setAttribute('id', id.replace(/\d+/, container.children.length));
        }
    });

    container.appendChild(variantField);
}

function removeVariant(button) {
    const container = document.getElementById('variant-fields-container');
    const variantField = button.closest('.variant-field');

    if (container.children.length > 1) {
        container.removeChild(variantField);
    }
}

        </script>
<script>
 $(document).ready(function() {
    function bindAttributeChangeEvent(attributeSelectElement) {
        $(attributeSelectElement).on('change', function() {
            var attributeId = $(this).val();
            var valueSelect = $(this).closest('.variant-field').find('#valueSelect');

            // Clear the value dropdown
            valueSelect.html('<option value="">{{ __('Select Value') }}</option>');

            if (attributeId) {
                $.ajax({
                    url: '{{ route("user.get.attribute.values") }}',
                    type: 'GET',
                    data: { attribute_id: attributeId },
                    success: function(response) {
                        if (response && response.values) {
                            // Populate the value dropdown
                            $.each(response.values, function(key, value) {
                                valueSelect.append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    }
                });
            }
        });
    }

    function addAttribute(button) {
        var container = document.getElementById('variant-fields-container');
        var currentField = button.closest('.variant-field');

        // Clone the current variant field
        var newField = currentField.cloneNode(true);

        // Reset the values of the cloned inputs
        newField.querySelectorAll('input, select').forEach(function(element) {
            if (element.tagName === 'SELECT') {
                element.selectedIndex = 0;
            } else if (element.tagName === 'INPUT') {
                element.value = '';
            }
        });

        // Bind the change event for the new attribute select element
        bindAttributeChangeEvent(newField.querySelector('#attributeSelect'));

        // Show the remove button in the new field
        newField.querySelector('.btn-danger').style.display = 'inline-block';

        // Append the new field to the container
        container.appendChild(newField);

        // Bind the removeAttribute function to the new minus button
        bindRemoveAttributeEvent(newField.querySelector('.btn-danger'));
    }

    function removeAttribute(button) {
        var currentField = button.closest('.variant-field');

        // Only remove the field if there's more than one present
        var variantFields = document.querySelectorAll('.variant-field');
        if (variantFields.length > 1) {
            currentField.remove();
        }
    }

    function bindRemoveAttributeEvent(button) {
        $(button).on('click', function() {
            removeAttribute(this);
        });
    }

    // Initial binding of events
    bindAttributeChangeEvent('#attributeSelect');
    bindRemoveAttributeEvent('.btn-danger');

    // Bind addAttribute function to window scope
    window.addAttribute = addAttribute;
});




    // document.addEventListener('DOMContentLoaded', function () {
    //     const addFieldButton = document.getElementById('add-field');
    //     const removeFieldButton = document.getElementById('remove-field');
    //     const variantFieldsContainer = document.getElementById('variant-fields-container');
    
    //     function updateButtonVisibility() {
    //         const fields = document.querySelectorAll('.variant-field');
    //         removeFieldButton.style.display = fields.length > 1 ? 'inline-block' : 'none';
    //     }
    
    //     addFieldButton.addEventListener('click', function () {
    //         const newField = document.querySelector('.variant-field').cloneNode(true);
    //         resetFieldValues(newField);
    //         variantFieldsContainer.insertBefore(newField, addFieldButton);
    //         updateButtonVisibility();
    //     });
    
    //     removeFieldButton.addEventListener('click', function () {
    //         const fields = document.querySelectorAll('.variant-field');
    //         if (fields.length > 1) {
    //             fields[fields.length - 1].remove();
    //             updateButtonVisibility();
    //         }
    //     });
    
    //     window.addVariantType = function (button) {
    //         const row = button.closest('.row');
    //         const newRow = document.createElement('div');
    //         newRow.classList.add('row', 'variant-field');
    //         newRow.innerHTML = `
    //           <div class='col-md-3 col-xl-3'>
    //                 <div class='mb-3'>
                       
    //                 </div>
    //             </div>
    //             <div class='col-md-3 col-xl-3'>
    //                 <div class='mb-3'>
    //                     <label class='form-label'>{{ __('Value') }}</label>
    //                     <div class="input-group mb-2">
    //                         <input type='text' class='form-control' name='variant_type[]' placeholder='{{ __('Small/ Black/ Steel') }}' value="">
    //                     </div>
    //                 </div>
    //             </div>
    //             <div class='col-md-3 col-xl-3'>
    //                 <div class='mb-3'>
    //                     <label class='form-label'>{{ __('Price') }}</label>
    //                     <div class="input-group mb-2">
    //                         <input type='number' class='form-control' name='variant_price[]' placeholder='{{ __('Variant Price') }}' value="">
    //                     </div>
    //                 </div>
    //             </div>
    //             <div class='col-md-3 col-xl-3'>
    //                 <div class='mb-3'>
    //                     <label class='form-label'>{{ __('Stock Status') }}</label>
    //                     <div class="input-group mb-2">
    //                         <select class='form-control' name='stock_status[]'>
    //                             <option value="Available">{{ __('Available') }}</option>
    //                             <option value="Not Available">{{ __('Not Available') }}</option>
    //                         </select>
    //                         <button type="button" class="btn btn-primary mx-2" onclick="addVariantType(this)">+</button>
    //                         <button type="button" class="btn btn-danger" onclick="removeVariantType(this)" style="display: none;">-</button>
    //                     </div>
    //                 </div>
    //             </div>
    //         `;
    //         row.parentElement.insertBefore(newRow, row.nextSibling);
    //         updateTypeButtonVisibility(newRow);
    //     };
    
    //     window.removeVariantType = function (button) {
    //         const row = button.closest('.row');
    //         const rows = row.parentElement.querySelectorAll('.row');
    //         if (rows.length > 1) {
    //             row.remove();
    //         }
    //         updateAllTypeButtonVisibility();
    //     };
    
    //     function resetFieldValues(field) {
    //         const inputs = field.querySelectorAll('input');
    //         inputs.forEach(input => input.value = '');
    //     }
    
    //     function updateTypeButtonVisibility(inputGroup) {
    //         const minusButtons = inputGroup.querySelectorAll('.btn-danger');
    //         minusButtons.forEach(button => button.style.display = 'inline-block');
    //     }
    
    //     function updateAllTypeButtonVisibility() {
    //         const fields = document.querySelectorAll('.variant-field');
    //         fields.forEach(field => {
    //             const inputGroups = field.querySelectorAll('.input-group');
    //             inputGroups.forEach((group, index) => {
    //                 const minusButton = group.querySelector('.btn-danger');
    //                 if (minusButton) {
    //                     minusButton.style.display = inputGroups.length > 1 ? 'inline-block' : 'none';
    //                 }
    //             });
    //         });
    //     }
    
    //     // Initialize button visibility
    //     updateButtonVisibility();
    // });
</script>
<script>
    function removeProduct(id) {
	"use strict";
        $("#"+id).remove();
    }

    function getRandomInt() {
        min = Math.ceil(0);
        max = Math.floor(9999999999);
        return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
    }

    function chooseImg(a){
        "use strict";
        var imgUri = $(a).attr('id');
        if (this.checked) {
            if($.inArray(imgUri, selectedImages)) {
                selectedImages.push(imgUri);
            } else {
                selectedImages.splice($.inArray(imgUri, selectedImages), 1);
            }
        } else {
            selectedImages.splice($.inArray(imgUri, selectedImages), 1);
        }
        $('.image1').val(selectedImages);
    }
</script>
<script type="text/javascript">
    Dropzone.options.dropzone = {
    maxFilesize: {{ env('SIZE_LIMIT') / 1024 }},
    acceptedFiles: ".jpeg,.jpg,.png,.gif",
    init: function () {
        this.on("success", function (file, response) {
            getnewimages();
        });
    }
};

var selectedImages = [];

function openMedia(id){
    "use strict";
    
    var currentSelection = id;
    selectedImages = [];
    $('.image1').val("");
    $(".media_image").prop("checked", false);
    $('#openMediaModel').modal('show');
    $("#modalImages .media_image").not(":checked").prop("disabled", false);
}

        // $(".media_image").on( "click", function() {
        //     var imgUri = $(this).attr('id');
        //     if (this.checked) {
        //         if($.inArray(imgUri, selectedImages)) {
        //             selectedImages.push(imgUri);
        //         } else {
        //             selectedImages.splice($.inArray(imgUri, selectedImages), 1);
        //         }
        //     } else {
        //         selectedImages.splice($.inArray(imgUri, selectedImages), 1);
        //     }
        //     $('.image1').val(selectedImages);
        // });
        
        
       
    // ///////////////////////////////////////////////////////////////////////////////  
       
$('#product_subtitle').keyup(function () {

  var characterCount = $(this).val().length,
    current = $('#current-title'),
    maximum = $('#maximum-title'),
    theCount = $('#the-count-title');

  current.text(characterCount);


  /*This isn't entirely necessary, just playin around*/
  if (characterCount < 50) {
    current.css('color', '#666');
  }
  if (characterCount >= 50 && characterCount < 100) {
    current.css('color', '#6d5555');
  }
  if (characterCount >= 100 && characterCount < 150) {
    current.css('color', '#793535');
  }
  if (characterCount >= 150 && characterCount < 200) {
    current.css('color', '#841c1c');
  }
  if (characterCount >= 200 && characterCount < 256) {
    current.css('color', '#8f0001');
  }

  if (characterCount >= 256) {
    maximum.css('color', '#8f0001');
    current.css('color', '#8f0001');
    theCount.css('font-weight', 'bold');
  } else {
    maximum.css('color', '#8f0001');
    theCount.css('font-weight', 'bold');
  }


});




// //////////////////////////////////////////////////////////////////////////////////
       
       
       
        
        
        

        
        
        
function existingTag(text)
{
	var existing = false,
		text = text.toLowerCase();
  
    
	$(".tags").each(function(){
	   
	    if ($(this).text().toLowerCase() == text) 
		{
			existing = true;
			return "";
		}
		
		
		
	});
    
    
	return existing;
}
$(function(){
  $(".tags-new input").focus();
   
  $(".tags-new input").keyup(function(){
 var text_data="";
		var tag = $(this).val().trim(),
		length = tag.length;

		if((tag.charAt(length - 1) == ',') && (tag != ","))
		{
			tag = tag.substring(0, length - 1);

			if(!existingTag(tag))
			{
				$('<li class="tags"><span>' + tag + '</span><i class="fa fa-times"></i></i></li>').insertBefore($(".tags-new"));
					$(".tags").each(function(){
	                text_data+=$(this).text()+",";
					});
	               // text_data+=tag;
	                $("#seo_keywords_data").val(text_data);
				$(this).val("");	
			}
			else
			{
				$(this).val(tag);
			}
		}
	});
  
  $(document).on("click", ".tags i", function(){
    $(this).parent("li").remove();
     var text_data="";
    	$(".tags").each(function(){
	                text_data+=$(this).text()+",";
					});
	               
	                $("#seo_keywords_data").val(text_data);
  });

});
        
        
        
        
        
        
</script>


<script>
    
    
    $('#assign_service_category').on('change', function () {
    var category_id = this.value;

    $('#assign_service_subcategory').html('');

    $.ajax({
        url: '{{ route('user.getSubcategories') }}?category_id=' + category_id,
        type: 'post',
        data: { _token: "{{ csrf_token() }}" },
        success: function (res) {
            $('#assign_subcategory').html('<option value="">Select Sub Category</option>');
            if (res.length === 0) {
                $('#assign_service_subcategory').append('<option value="0">No Sub Category Available</option>');
            } else {
                $.each(res, function (key, value) {
                    $('#assign_service_subcategory').append('<option value="' + value.sub_category_id + '">' + value.sub_category_name + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
});
        
            
    $('#assign_category').on('change', function () {
    var category_id = this.value;

    $('#assign_subcategory').html('');

    $.ajax({
        url: '{{ route('user.getSubcategories') }}?category_id=' + category_id,
        type: 'post',
        data: { _token: "{{ csrf_token() }}" },
        success: function (res) {
            $('#assign_subcategory').html('<option value="">Select Sub Category</option>');
            if (res.length === 0) {
                $('#assign_subcategory').append('<option value="0">No Sub Category Available</option>');
            } else {
                $.each(res, function (key, value) {
                    $('#assign_subcategory').append('<option value="' + value.sub_category_id + '">' + value.sub_category_name + '</option>');
                });
            }
        },
        error: function (xhr, status, error) {
            console.error('Error fetching data:', error);
        }
    });
});


         
    function getnewimages()
{
    
    var card_id = "{{$business_card->card_id}}";
     var image_type = 1;
  var new_Images="";
    // Fetch cities based on the selected state
    $.ajax({
        url: '{{ route('user.getNewImages') }}',
        type: 'post',
        data:{_token: "{{ csrf_token() }}","card_id":card_id,"image_type":image_type},
        success: function (res) {
                    
                    
               
                   $('#modalImages').empty(); 
                    
                    

            $.each(res, function (key, value) {
                 new_Images+='<div class="col-6 col-sm-4"><label class="form-imagecheck mb-2"><input name="form-imagecheck_data" type="checkbox" value="'+value.media_url+'" id="'+value.id+'" onclick="checkforselected('+value.id+')"  class="form-imagecheck-input media_image"><span class="form-imagecheck-figure"><img src="'+value.image_url+'" class="form-imagecheck-image" ></span></label></div>';
                    
            });
            $('#modalImages').append(new_Images); 
               
        },
        error: function (xhr, status, error) {
            console.error('Error fetching cities:', error);
        }
    });

}
      function checkforselected(value_id) {
        var imgUri = $("#" + value_id).attr('value');
    
        if ($("#" + value_id).is(':checked')) {
    
            if ($.inArray(imgUri, selectedImages) === -1) {
                // Add the image URI to selectedImages array only if it's not already present
                selectedImages.push(imgUri);
            }
    
            if (selectedImages.length > 3) {
                // If more than three images are selected, uncheck the last selected checkbox
                $("#" + value_id).prop("checked", false);
            } else {
                $('.image1').val(selectedImages);
            }
        } else {
            // Remove the image URI from selectedImages array
            selectedImages.splice($.inArray(imgUri, selectedImages), 1);
            $('.image1').val(selectedImages);
        }
    
        // Disable unchecked checkboxes if the limit is reached
        $("#modalImages .media_image").not(":checked").prop("disabled", selectedImages.length >= 3);
        $('#images_count_uploaded').text(selectedImages.length);
        previewimagestodiv();
    }









    function previewimagestodiv()
 {
    
    $('#images_count_uploaded').text(selectedImages.length);
    $('#iamges_preview').empty();
    $(selectedImages).each(function(key, value) {
         
            images="{{ url('/public') }}/"+value;
            data_function="product_image_delete_id"+key;
  $('#iamges_preview').append("<div class='col-md-3 images_preview'><img class='w-100'  src='" + images + "' /> <i class='fa fa-times-circle' id='"+data_function+"'></i></div>");

  $('#'+data_function).attr('onclick', 'delete_selected_image("'+value+'");');
});


 }

 function delete_selected_image(imgUri)
{
    selectedImages.splice($.inArray(imgUri, selectedImages), 1);
    $('.image1').val(selectedImages);
    previewimagestodiv();


}
</script>












@endsection

<!--@section('script')-->
<!--<script>-->
    

<!--    function removeProduct(id) {-->
<!--	"use strict";-->
<!--        $("#"+id).remove();-->
<!--    }-->

<!--    function getRandomInt() {-->
<!--        min = Math.ceil(0);-->
<!--        max = Math.floor(9999999999);-->
        return Math.floor(Math.random() * (max - min) + min); //The maximum is exclusive and the minimum is inclusive
<!--    }-->

<!--    function chooseImg(a){-->
<!--        "use strict";-->
<!--        var imgUri = $(a).attr('id');-->
<!--        if (this.checked) {-->
<!--            if($.inArray(imgUri, selectedImages)) {-->
<!--                selectedImages.push(imgUri);-->
<!--            } else {-->
<!--                selectedImages.splice($.inArray(imgUri, selectedImages), 1);-->
<!--            }-->
<!--        } else {-->
<!--            selectedImages.splice($.inArray(imgUri, selectedImages), 1);-->
<!--        }-->
<!--        $('.image1').val(selectedImages);-->
<!--    }-->
<!--</script>-->
<!--<script type="text/javascript">-->
<!--    Dropzone.options.dropzone = {-->
<!--            maxFilesize  : {{ env('SIZE_LIMIT')/1024 }},-->
<!--            acceptedFiles: ".jpeg,.jpg,.png,.gif",-->
<!--            init: function() {-->
<!--            this.on("success", function(file, response) {-->
<!--                var uploadImages = `<div class="col-6 col-sm-4">-->
<!--                        <label class="form-imagecheck mb-2">-->
<!--                          <input name="form-imagecheck" type="checkbox" id="../../images/`+response.image_url+`" class="form-imagecheck-input media_image" onclick="chooseImg(this)">-->
<!--                          <span class="form-imagecheck-figure">-->
<!--                            <img src="../../images/`+response.image_url+`" class="form-imagecheck-image">-->
<!--                          </span>-->
<!--                        </label>-->
<!--                    </div>`;-->

<!--                $("#captions").append(uploadImages).html();-->

<!--                $('.image1').val(`/images/`+response.image_url);-->

<!--                $('#openMediaModel').modal('hide');-->

                // Hidden empty
<!--                $(".empty").hide();-->
<!--            });-->
<!--        }-->
<!--        };-->

<!--        var selectedImages = "";-->
<!--        function openMedia(id){-->
<!--            "use strict";-->
            
<!--            var currentSelection = id;-->
<!--            selectedImages = [];-->
<!--            $('.image1').val("");-->
<!--            $(".media_image").prop("checked", false);-->
<!--            $('#openMediaModel').modal('show');-->
<!--        }-->

<!--        $(".media_image").on( "click", function() {-->
<!--            var imgUri = $(this).attr('id');-->
<!--            if (this.checked) {-->
<!--                if($.inArray(imgUri, selectedImages)) {-->
<!--                    selectedImages.push(imgUri);-->
<!--                } else {-->
<!--                    selectedImages.splice($.inArray(imgUri, selectedImages), 1);-->
<!--                }-->
<!--            } else {-->
<!--                selectedImages.splice($.inArray(imgUri, selectedImages), 1);-->
<!--            }-->
<!--            $('.image1').val(selectedImages);-->
<!--        });-->
<!--</script>-->


<!--<script>-->
    
    
    
        
            
<!--  $('#assign_category').on('change', function () {-->
<!--    var category_id = this.value;-->

<!--$('#assign_subcategory').html('');-->

<!--    $.ajax({-->
<!--        url: '{{ route('user.getSubcategories') }}?category_id=' + category_id,-->
<!--        type: 'post',-->
<!--        data:{_token: "{{ csrf_token() }}"},-->
<!--        success: function (res) {-->
<!--            $('#assign_subcategory').html('<option value="">Select Sub Category</option>');-->
<!--           $('#assign_subcategory').append('<option value="0">Not Sub Category Applicable</option>');-->
<!--            $.each(res, function (key, value) {-->
<!--                $('#assign_subcategory').append('<option value="' + value.sub_category_id + '">' + value.sub_category_name + '</option>');-->
<!--            });-->
<!--        },-->
<!--        error: function (xhr, status, error) {-->
<!--            console.error('Error fetching data:', error);-->
<!--        }-->
<!--    });-->
<!--});-->

        
<!--</script>-->












<!--@endsection-->
@endsection