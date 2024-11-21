@extends('layouts.user', ['header' => true, 'nav' => true, 'demo' => true, 'settings' => $settings])

@section('css')
<link href="{{asset('backend/css/dropzone.min.css')}}" rel="stylesheet">
<script src="{{asset('backend/js/dropzone.min.js')}}"></script>
<style>
.modal-body .row-cards .form-imagecheck .form-imagecheck-input {position: absolute;left: 10%;top: 10%;width: 12%;height: 11%;z-index: 1;}
.form-imagecheck-image {max-width: 100%;width: 100%;opacity: 0.64;border-radius:10px;transition:0.5s;}
.form-imagecheck-image:hover {opacity: 1;}
.row-cards .form-imagecheck .form-imagecheck-input:checked ~ .form-imagecheck-figure img {opacity:1;border:1px solid #0054a6;}
.tags-input {list-style : none;border:1px solid #ccc;display:inline-block;padding:5px;height: 26px;font-size:14px;background:#f3f3f3;width: 600px;border-radius:2px;overflow:hidden;}
.tags-input li{float:left;}
.tags{background:#195FA6;padding:5px 20px 5px 8px;border-radius:2px;margin-right: 5px;position: relative;color:#fff;}
.tags i{position: absolute; right:6px;top:3px;width: 8px;height: 8px;content:'';cursor:pointer;opacity: .7;font-size:12px;}
.tags i:hover{opacity: 1;}
.tags-new input[type="text"]{border:0;margin: 0;padding: 0 0 0 3px;font-size: 14px;margin-top: 5px;background:transparent;}
.tags-new input[type="text"]:focus{outline:none;}


</style>
@endsection

@section('content')

        <div class="container-fluid">
            <div class="row row-deck row-cards">
                <div class="col-sm-12 col-lg-12">
                    <form action="{{ route('user.update.products', [$store_id,$products->product_id]) }}" method="post"
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
                        
                            // Check if the user's plan exists
                            if ($plan) {
                                $active_plan = json_decode($plan->plan_details);
                            }
                        
                            $plugin = null;
                            $plugin_inventory = null;
                            $plugin_variants = null;

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
                        
                            // Ensure active_plan and plan_id exist before querying plugins
                            if ($active_plan && isset($active_plan->plan_id)) {
                                $plugin = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Book An Appointment' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
                        
                                $plugin_inventory =  $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Inventory Management' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first();
                        
                                $plugin_variants = $menu_data->filter(function ($plugin) use ($active_plan) {
        return $plugin->plan_id == $active_plan->plan_id 
            && $plugin->plugin_name == 'Product Variants' 
            && $plugin->is_active == 1 
            && $plugin->is_deleted == 0;
    })->first(); 
                            }
                        
                            // Fetch appointment extension if the plugin exists
                            $appointment = null;
                            if ($plugin) {
                                $appointment = Extension::where('plugin_id', $plugin->id)
                                    ->where('card_id', $store_id)
                                    ->where('is_active', 1)
                                    ->where('is_deleted', 0)
                                    ->first(); // Make sure to use first()
                            }
                        
                            // Fetch product variants extension if the plugin exists
                            $productvariants = null;
                            if ($plugin_variants) {
                                $productvariants = Extension::where('plugin_id', $plugin_variants->id)
                                    ->where('card_id', $store_id)
                                    ->where('is_active', 1)
                                    ->where('is_deleted', 0)
                                    ->first(); // Make sure to use first()
                            }
                        
                            // Fetch inventory extension if the plugin exists
                            $inventory = null;
                            if ($plugin_inventory) {
                                $inventory = Extension::where('plugin_id', $plugin_inventory->id)
                                    ->where('card_id', $store_id)
                                    ->where('is_active', 1)
                                    ->where('is_deleted', 0)
                                    ->first(); // Make sure to use first()
                            }
                            // dd($plugin, $plugin_inventory, $plugin_variants);
@endphp

                            @if(isset($appointment))
                            <div class="row m-0">
                                <div class="col-xl-12">
                                    <div class="row">
                                        <h2 class="page-title my-3">
                                            {{ __('Products / Services') }}
                                        </h2>
                                        <?php $i = 0; ?>
                                        <div class='row' id="{{ $i }}">
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required' for='product_status'><strong>{{ __('Categories') }}</strong><span class = "text-danger">*</span></label>
                                                    <select name='categories[]' id='assign_category' class='form-control' required>
                                                        <option value=''>Select Category</option>
                                                        @if($products->category_id=='')
                                                        <option value='' selected>{{ __('Uncategorized') }}</option>   
                                                        @endif
                                                        @foreach ($categories as $category)
                                                        <option value='{{ $category->category_id }}' {{ $category->category_id == $products->category_id ? 'selected' : '' }}>{{ __($category->category_name) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required' for='product_status'><strong>{{ __('Sub Categories') }}</strong><span class = "text-danger">*</span></label>
                                                    <select name='sub_category[]' id='assign_subcategory' class='form-control' required>
                                                        <option value=''>Select Sub Category</option>
                                                        <option value='0' {{  ($products->sub_category_id==0) ? 'selected' : '' }}>No Sub Category Available</option>
                                                        @foreach ($sub_categories as $sub_category)
                                                        <option value='{{ $sub_category->sub_category_id }}' {{ $sub_category->sub_category_id == $products->sub_category_id ? 'selected' : '' }}>{{ __($sub_category->sub_category_name) }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6 d-none'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Product Badge') }}</strong><span class = "text-danger">*</span></label>
                                                    <input type='text' value="123" class='form-control' name='badge[]'
                                                        placeholder='{{ __(' Product Badge') }}...'
                                                        value="{{ $products->badge }}" required>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required'><strong>{{ __('Product / Service Image') }}</strong><span class = "text-danger">*</span> <small>(Image should be 800*800) Image Resize :<a href="https://imageresizer.com/" target="blank"><b>Click Here.</b></a></small>
                                                
                                                
                                                
                                                </label>
                                                    <div class='input-group mb-2' style="justify-content: space-between;align-items: center;">
                                                        <input type='hidden'
                                                            class='image{{ $products->id }} media-model form-control'
                                                            name='product_image[]' placeholder='{{ __(' Product / Service Image')
                                                            }}' value="{{ $products->product_image }}" required readonly>
                                                         <label class='mb-0'>
                                                            <strong><span id="images_count_uploaded"> {{ count(explode(',',$products->product_image)) }}</span> Images has been uploaded.</strong>
                                                         </label>
                                                         

                                                        <button class='btn btn-primary btn-md' type='button'
                                                            onclick="openMedia({{ $products->id }})">{{ __('Choose
                                                            image') }}</button>
                                                    </div>
                                                   
                                                    <div id="iamges_preview" class="row">
                                                        @foreach(explode(',',$products->product_image) as $images)
                                                        <div class='col-md-4 images_preview' id="product_image_id"><img class='w-100'  src='{{asset($images) }}' /> <i class='fa fa-times-circle' onclick="delete_selected_image('{{ $images }}');"></i></div>
                                                        
                                                        @endforeach
                                                    </div>
                                                   <small> Max 3 Images Allowed</small>
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6'>
                                                <div class = row>
                                                    <div class='col-md-6 col-xl-6'>
                                                        <div class='mb-3'>
                                                            <label class='form-label required'><strong>{{ __('Product / Service Name') }}</strong><span class = "text-danger">*</span></label>
                                                            <input type='text' class='form-control' name='product_name[]'
                                                                placeholder='{{ __(' Product / Service Name') }}'
                                                                value="{{ $products->product_name }}" required>
                                                        </div>
                                                    </div>
                                                    <div class='col-md-6 col-xl-6'>
                                                        <div class='mb-3'>
                                                            <label class='form-label required'><strong>{{ __('SKU') }}</strong></label>
                                                            <input type='text' class='form-control' pattern="[a-zA-Z0-9]+" name='sku[]'
                                                                placeholder='{{ __('SKU') }}'
                                                                value="{{ $products->sku }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class='col-md-12 col-xl-12'>
                                                <div class='mb-3'>
                                                  <label class='form-label required'><strong>{{ __('Product / Service Description') }}</strong><span class="text-danger">*</span></label>
                                                  <textarea class='form-control' maxlength="512" name='product_subtitle[]' id="product_subtitle"
                                                    data-bs-toggle='autosize' placeholder='{{ __('Product / Service Description') }}...' required>{!! $products->product_subtitle !!}</textarea>
                                                  <div>
                                                    <span id="current-title">0</span> / <span id="maximum-title">512</span> {{ __('characters') }}
                                                  </div>
                                                </div>
                                              </div>
                                              <div class='col-md-6 col-xl-6'>
                                                <div class='row' >
                                                 <div class='col-md-6 col-xl-6'>
                                                    <label class='form-label required'>{{ __('Product / Service Documentation
                                                        Title') }}</label>
                                                    <input type='text' class='form-control' name='vedio_title[]'
                                                        min='1' placeholder='{{ __(' Product / Service Documentation
                                                        Title') }}'
                                                        value="{{ $products->video_title }}" min='1' step='.001'
                                                        >
                                                     </div>
                                                  <div class='col-md-6 col-xl-6'>
                                                    <label class='form-label'>{{ __('Product / Service Documentation Icon') }}</label>
                                                    <div class="d-flex justify-content-between">
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-png" value="jpg" @if(isset($products) && $products->video_icon == 'jpg') checked @endif>
                                                            <label class="form-check-label" for="icon-png">
                                                                <i class="fa fa-image"></i>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-video" value="video" @if(isset($products) && $products->video_icon == 'video') checked @endif>
                                                            <label class="form-check-label" for="icon-video">
                                                                <i class="fa fa-play"></i>
                                                            </label>
                                                        </div>
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-document" value="document" @if(isset($products) && $products->video_icon == 'document') checked @endif>
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
                                                    <label class='form-label required'>{{ __('Product / Service Documentation
                                                        Link') }}</label>
                                                    <input type='text' class='form-control' name='vedio_link[]'
                                                        min='1' step='.001' value="{{ $products->video_link }}"
                                                        placeholder='{{ __(' Product Documentation Link') }}'>
                                                     </div>
                                                </div>
                                           
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='row' >
                                                 <div class='col-md-6 col-xl-6'>
                                                     <label class='form-label required'>{{ __('No of units') }}<span class = "text-danger">*</span></label>
                                                    <input type='number' class='form-control' name='no_of_units[]'
                                                        min='1' placeholder='{{ __(' No of units') }}...'
                                                        value="{{ $products->no_of_units }}" min='1' 
                                                        required>
                                                     </div>
                                                  <div class='col-md-6 col-xl-6'>
                                                      <label class='form-label required'>{{ __('Unit') }}<span class = "text-danger">*</span></label>
                                                   
                                                        <select name="units[]" class="form-control" >
                                                            <option value="">Select Unit</option>
                                                            @foreach($units as $units_data)
                                                                <option value="{{$units_data->unit_shortname}}" {{ ($products->units==$units_data->unit_shortname)?"selected":"" }}>{{$units_data->unit_name}}</option>
                                                            @endforeach
                                                        </select>
                                                     </div>
                                                </div>
                                            </div>
                                             <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required' for='product_price_status'>{{
                                                        __('Price Show/Hide') }}<span class = "text-danger">*</span></label>
                                                    <select name='product_price_status[]' id='product_price_status'
                                                        class='form-control' required>
                                                        <option value='1' {{ ($products->show_price ==1) ? "selected" : "" }}> {{ __('Show Price') }}</option>
                                                        <option value='0' {{ ($products->show_price ==0) ? "selected" : "" }}>
                                                            {{ __('Hide Price') }}</option>
                                                    </select>
                                                    <!--<a href='#' class='btn mt-3 btn-danger btn-sm'-->
                                                    <!--    onclick='removeProduct({{ $i }})'>{{ __('Remove') }}</a>-->
                                                </div>
                                            </div>
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'><label class='form-label required'>{{ __('Regular
                                                        Price') }}<span class = "text-danger">*</span></label>
                                                    <input type='number' class='form-control' name='regular_price[]'
                                                        min='1' placeholder='{{ __(' Regular Price') }}...'
                                                        value="{{ $products->regular_price }}" min='1' step='.001'
                                                        required>
                                                </div>
                                            </div>
                                            
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required'>{{ __('Sales Price') }}<span class = "text-danger">*</span></label>
                                                    <input type='number' class='form-control' name='sales_price[]'
                                                        min='1' step='.001' value="{{ $products->sales_price }}"
                                                        placeholder='{{ __(' Sales Price') }}...' required>
                                                </div>
                                            </div>
                                              
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
                                <div class="row m-0" id = "productDetails" style = "display: none">
                                    <div class="col-xl-12">
                                        <div class="row">
                                            <h5 class="page-title my-3">
                                                {{ __('Product Details') }}
                                            </h5>
                                            <?php $i = 0; ?>
                                            <div class='row' id="{{ $i }}">
                                              @php
                           
                            $plan = DB::table('users')
                                      ->where('user_id', Auth::user()->user_id)
                                      ->where('status', 1)
                                      ->first();
                            $plan_details = DB::table('plans')
                                              ->where('plan_id', $plan->plan_id)
                                              ->first();
                                             
                            $maxVariants = $plan_details->no_of_variants;
                            @endphp
                                          @if (isset($productvariants))
                                          {{-- <div class='col-md-6 col-xl-6'>
                                            <label class='form-label'><strong>{{ __('Product Variants') }}</strong></label>
                                           
                                        </div>
                                          <div class = "col-md-6 col-xl-6">
                                            <div class = "row">
                                                <div class = "col-md-6 col-xl-6">
                                                  
                                                        <div class="form-check form-check-inline">
                                                            <input class="form-check-input" type="radio" name="variants_check" id="variants-no" value="No" @if(isset($products) && $products->variants_check == 'N') checked @endif onchange="toggleVariantFields()">
                                                            <label class="form-check-label" for="icon-jpg">No</label>
                                                        </div>
                                                      
                                                    
                                                </div>
                                                <div class = "col-md-6 col-xl-6">
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="variants_check" id="variants-yes" value="Yes" @if(isset($products) && $products->variants_check == 'Y') checked @endif onchange="toggleVariantFields()">
                                                        <label class="form-check-label" for="icon-video">Yes</label>
                                                    </div>
                                                
                                            </div>
                                            </div>
                                        </div> --}}
                                              <div class='col-md-12 col-xl-12' id="variant-fields-container" >
                                                      @forelse($variants as $index => $variant)
                                                          <div class='row variant-field'>
                                                              @if(isset($attributes) && $attributes->count() > 0)
                                                                  @foreach($attributes as $attribute)
                                                                      <div class='col-md-3 col-xl-3'>
                                                                          <div class='mb-3'>
                                                                              <label class="form-label required"><strong>{{ $attribute->name }}</strong></label>
                                                                              <select name="attributes[{{ $index }}][{{ $attribute->id }}][]" id="attributeSelect_{{ $index }}_{{ $attribute->id }}" class="form-control dynamic-attribute-select">
                                                                                  @foreach($attribute->values as $value)
                                                                                  @if($value->is_deleted == 0)
                                                                                      <option value="{{ $value->id }}" {{ in_array($value->id, json_decode($variant->attributes, true)[$attribute->id] ?? []) ? 'selected' : '' }}>
                                                                                          {{ $value->name }}
                                                                                      </option>
                                                                                    @endif
                                                                                  @endforeach
                                                                              </select>
                                                                          </div>
                                                                      </div>
                                                                  @endforeach
                                                              @endif
                                                              @if (isset($inventory))
                                                              <div class='col-md-3 col-xl-3'>
                                                                  <div class='mb-3'>
                                                                      <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                                                      <div class="input-group mb-2">
                                                                          <input type='number' class='form-control' name='prices[{{ $index }}]' placeholder='{{ __('Price') }}' value="{{ $variant->price }}">
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              <div class='col-md-3 col-xl-3'>
                                                                  <div class='mb-3'>
                                                                      <label class='form-label'>{{ __('Stock Quantity') }}</label>
                                                                      <div class='input-group mb-2'>
                                                                          <input type='number' class='form-control stock-input' name='stock_statuses[{{ $index }}]' placeholder='{{ __('Quantity') }}' value="{{ $variant->stock_status }}" oninput="updateTotalStockQuantity()">
                                                                          <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                                          <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              @else
                                                              <div class='col-md-3 col-xl-3'>
                                                                <div class='mb-3'>
                                                                    <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                                                    <div class="input-group mb-2">
                                                                        <input type='number' class='form-control' name='prices[{{ $index }}]' placeholder='{{ __('Price') }}' value="{{ $variant->price }}">
                                                                        <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                                          <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            @endif
                                                          </div>
                                                      @empty
                                                          <!-- If no variants, add empty fields for the first variant -->
                                                          <div class='row variant-field'>
                                                              @foreach($attributes as $index => $attribute)
                                                                  <div class='col-md-3 col-xl-3'>
                                                                      <div class='mb-3'>
                                                                          <label class="form-label required">{{ $attribute->name }}</label>
                                                                          <select name="attributes[0][{{ $attribute->id }}][]" class="form-control dynamic-attribute-select">
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
                                                              @if (isset($inventory))
                                                              <div class='col-md-3 col-xl-3'>
                                                                  <div class='mb-3'>
                                                                      <label class='form-label'>{{ __('Price') }}</label>
                                                                      <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}'>
                                                                  </div>
                                                              </div>
                                                              <div class='col-md-3 col-xl-3'>
                                                                  <div class='mb-3'>
                                                                      <label class='form-label'>{{ __('Stock Quantity') }}</label>
                                                                      <div class='input-group'>
                                                                          <input type='number' class='form-control stock-input' name='stock_statuses[0]' placeholder='{{ __('Quantity') }}' value="" oninput="updateTotalStockQuantity()">
                                                                          <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                                          <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                                      </div>
                                                                  </div>
                                                              </div>
                                                              @else
                                                              <div class='col-md-3 col-xl-3'>
                                                                <div class='mb-3'>
                                                                    <label class='form-label'>{{ __('Price') }}</label>
                                                                    <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}'>
                                                                    <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                                    <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                                </div>
                                                            </div>
                                                              @endif
                                                          </div>
                                                      @endforelse
                                                 
                                              </div>
                                          @endif
                                            
                                           
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label required' for='product_status'>{{
                                                        __('Status') }}<span class = "text-danger">*</span></label>
                                                    <select name='product_status[]' id='product_status'
                                                        class='form-control' required>
                                                        <option value='instock' {{ $products->product_status ==
                                                            "instock" ? "selected" : "" }}>
                                                            {{ __('In Stock') }}</option>
                                                        <option value='outstock' {{ $products->product_status ==
                                                            "outstock" ? "selected" : "" }}>
                                                            {{ __('Out of Stock') }}</option>
                                                    </select>
                                                    <!--<a href='#' class='btn mt-3 btn-danger btn-sm'-->
                                                    <!--    onclick='removeProduct({{ $i }})'>{{ __('Remove') }}</a>-->
                                                </div>
                                            </div>
                                        @if (isset($productvariants))
                                        @if($variants->isNotEmpty())
                                        @if (isset($inventory) )
                                        <div class='col-md-6 col-xl-6'>
                                            <div class='row' >
                                             <div class='col-md-6 col-xl-6'>
                                                <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                                 </div>
                                                 <div class='col-md-6 col-xl-6'>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                            <script>
                                                $(document).ready(function() {
                                                    $('.toggle-status').on('change', function() {
                                                        let productId = $(this).data('product-id');
                                                        let $this = $(this); // Reference to the checkbox element
                                                        let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                              
                                                        $.ajax({
                                                            url: '{{ route("user.toggleProductStatus") }}',
                                                            type: 'POST',
                                                            data: {
                                                                _token: '{{ csrf_token() }}',
                                                                product_id: productId,
                                                                status: status
                                                            },
                                                            success: function(response) {
                                                                if (response.success) {
                                                                    // Use response.status to reflect the updated status
                                                                    alert('Product status updated successfully!');
                                                                } else {
                                                                    alert('Failed to update product status.');
                                                                }
                                                            },
                                                            error: function() {
                                                                alert('Error occurred while updating product status.');
                                                            }
                                                        });
                                                    });
                                                });
                                              </script>
                                        @endif
                                        @else
                                        @if (isset($inventory))
                                        <div class='col-md-6 col-xl-6'>
                                            <div class='row' >
                                             <div class='col-md-6 col-xl-6'>
                                                <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                                 </div>
                                                 <div class='col-md-6 col-xl-6'>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                                    </div>
                                                </div>
                                                
                                        </div>
                                            <script>
                                                $(document).ready(function() {
                                                    $('.toggle-status').on('change', function() {
                                                        let productId = $(this).data('product-id');
                                                        let $this = $(this); // Reference to the checkbox element
                                                        let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                              
                                                        $.ajax({
                                                            url: '{{ route("user.toggleProductStatus") }}',
                                                            type: 'POST',
                                                            data: {
                                                                _token: '{{ csrf_token() }}',
                                                                product_id: productId,
                                                                status: status
                                                            },
                                                            success: function(response) {
                                                                if (response.success) {
                                                                    // Use response.status to reflect the updated status
                                                                    alert('Product status updated successfully!');
                                                                } else {
                                                                    alert('Failed to update product status.');
                                                                }
                                                            },
                                                            error: function() {
                                                                alert('Error occurred while updating product status.');
                                                            }
                                                        });
                                                    });
                                                });
                                              </script>
                                        </div>
                                        @endif
                                        @endif
                                        @else
                                        @if (isset($inventory))
                                        <div class='col-md-6 col-xl-6'>
                                            <div class='row' >
                                             <div class='col-md-6 col-xl-6'>
                                                <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                        <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                                 </div>
                                                 <div class='col-md-6 col-xl-6'>
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                        <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                        <script>
                                            $(document).ready(function() {
                                                $('.toggle-status').on('change', function() {
                                                    let productId = $(this).data('product-id');
                                                    let $this = $(this); // Reference to the checkbox element
                                                    let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                          
                                                    $.ajax({
                                                        url: '{{ route("user.toggleProductStatus") }}',
                                                        type: 'POST',
                                                        data: {
                                                            _token: '{{ csrf_token() }}',
                                                            product_id: productId,
                                                            status: status
                                                        },
                                                        success: function(response) {
                                                            if (response.success) {
                                                                // Use response.status to reflect the updated status
                                                                alert('Product status updated successfully!');
                                                            } else {
                                                                alert('Failed to update product status.');
                                                            }
                                                        },
                                                        error: function() {
                                                            alert('Error occurred while updating product status.');
                                                        }
                                                    });
                                                });
                                            });
                                          </script>
                                        @endif
                                        @endif
                                            
                                            <div id="step-7" class="content col-md-12 col-xl-12">
                                                <?php
                                                    $seo_data=json_decode($products->products_tags,true);
                                                ?>
                                                <div class="row">
                                                    <div class="col-md-12 col-xl-12 col-sm-12">
                                                        <div class="mb-3 bootstrap-tagsinput"> 
                                                        <label class="form-label required">{{ __('SEO Tags') }} <small> [After each tag enter Comma ( , )  ]</small></label>
                                                             <ul class="tags-input form-control d-table">
                                                                 <?php 
                                                                 $oldtags=array();
                                                                 if(isset($seo_data['seo_keywords']) && !empty($seo_data['seo_keywords']))
                                                                 {
                                                                    $oldtags=explode(",",$seo_data['seo_keywords']) ;
                                                                 }
                                                                 
                                                                 ?>
                                                                 @foreach($oldtags as $tags)
                                                                 @if($tags)
                                                                <li class="tags">{{$tags}}<i class="fa fa-times"></i></li>
                                                                @endif
                                                                @endforeach
                                                                <li class="tags-new">
                                                                  <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                                                </li>
                                                              </ul>  
                                                              <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords[]" id="seo_keywords_data"> 
                                                                <!-- <input type="text" class="form-control " -->
                                                                <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                                                <!--required>-->
                                                        </div>
                                                    </div>
                                                </div> 
                                            </div>

                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        <div class="row m-0" id = "serviceDetails" style = "display: none">
                            <div class="col-xl-12">
                                <div class="row">
                                    <h5 class="page-title my-3">
                                        {{ __('Service Details') }}
                                    </h5>
                                    <?php $i = 0; ?>
                                    <div class='row' id="{{ $i }}">
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required' for='service_status'>{{
                                                __('Status') }}<span class = "text-danger">*</span></label>
                                            <select name='service_status[]' id='product_status'
                                                class='form-control' required>
                                                <option value='Available' {{ $products->service_status ==
                                                    "Available" ? "selected" : "" }}>
                                                    {{ __('Available') }}</option>
                                                <option value='Not Available' {{ $products->service_status ==
                                                    "Not Available" ? "selected" : "" }}>
                                                    {{ __('Not Available') }}</option>
                                            </select>
                                            <!--<a href='#' class='btn mt-3 btn-danger btn-sm'-->
                                            <!--    onclick='removeProduct({{ $i }})'>{{ __('Remove') }}</a>-->
                                        </div>
                                    </div>
                                    <div class='col-md-6 col-xl-6'>
                                        <div class='mb-3'>
                                            <label class='form-label required' for='appointment'><strong>{{ __('Book An Appointment') }}</strong><span class="text-danger">*</span></label>
                                            <select name='appointment[]' id='appointment' class='form-control' onchange="toggleAppointmentFields()">
                                                <option value='1' {{ $products->appointment == 1 ? 'selected' : '' }}>{{ __('Yes') }}</option>
                                                <option value='0' {{ $products->appointment == 0 ? 'selected' : '' }}>{{ __('No') }}</option>
                                                <option value='2' {{ $products->appointment == 2 ? 'selected' : '' }}>{{ __('Customized Slots') }}</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div id="appointmentFields" style="display: none;" class='col-md-12 col-xl-12'>
                                        <div class='row'>
                                            <!-- Dynamic attribute fields -->
                                            {{-- <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class="form-label required"><strong>Date</strong></label>
                                                    <div class="input-group mb-2">
                                                        <input type='date' class='form-control' name='slot_date[]' placeholder='{{ __('Date') }}' value="{{ $products->slot_date }}">
                                                    </div>
                                                </div>
                                            </div> --}}
                                    
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slot Duration') }}</strong></label>
                                                    <div class="input-group mb-2">
                                                        <input type='number' class='form-control' name='slot_duration[]' placeholder='{{ __('Slot Duration') }}' value="{{ $products->slot_duration }}">
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Break Duration') }}</strong></label>
                                                    <div class="input-group mb-2">
                                                        <input type='number' class='form-control' name='slot_break[]' placeholder='{{ __('Slot Break') }}' value="{{ $products->slot_break }}">
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slots Start') }}</strong></label>
                                                    <div class='input-group mb-2'>
                                                        <input type='time' class='form-control' name='slot_start[]' placeholder='{{ __('Slots Start') }}' value="{{ $products->slot_start }}">
                                                    </div>
                                                </div>
                                            </div>
                                    
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slots End') }}</strong></label>
                                                    <div class='input-group mb-2'>
                                                        <input type='time' class='form-control' name='slot_end[]' placeholder='{{ __('Slots End') }}' value="{{ $products->slot_end }}">
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
                                                        <input type='text' id="multi-date-picker" class='form-control' name='cust_slot_date[]' placeholder='{{ __('Select Dates') }}' value="{{ $products->slot_date }}">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slot Duration') }}</strong></label>
                                                    <div class="input-group mb-2">
                                                        <input type='number' class='form-control' name='cust_slot_duration[]' placeholder='{{ __('Slot Duration') }}' value="{{ $products->slot_duration }}">
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Break Duration') }}</strong></label>
                                                    <div class="input-group mb-2">
                                                        <input type='number' class='form-control' name='cust_slot_break[]' placeholder='{{ __('Slot Break') }}'  value="{{ $products->slot_break }}">
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slots Start') }}</strong></label>
                                                    <div class='input-group mb-2'>
                                                        <input type='time' class='form-control' name='cust_slot_start[]' placeholder='{{ __('Slots Start') }}'  value="{{ $products->slot_start }}">
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div class='col-md-6 col-xl-6'>
                                                <div class='mb-3'>
                                                    <label class='form-label'><strong>{{ __('Slots End') }}</strong></label>
                                                    <div class='input-group mb-2'>
                                                        <input type='time' class='form-control' name='cust_slot_end[]' placeholder='{{ __('Slots End') }}'  value="{{ $products->slot_end }}">
                                                    </div>
                                                </div>
                                            </div>
                                
                                        </div>
                                    </div>
                                    
                                    <div id="step-7" class="content col-md-12 col-xl-12">
                                        <?php
                                            $seo_data=json_decode($products->products_tags,true);
                                        ?>
                                        <div class="row">
                                            <div class="col-md-12 col-xl-12 col-sm-12">
                                                <div class="mb-3 bootstrap-tagsinput"> 
                                                <label class="form-label required">{{ __('SEO Tags') }} <small> [After each tag enter Comma ( , )  ]</small></label>
                                                     <ul class="tags-input form-control d-table">
                                                         <?php 
                                                         $oldtags=array();
                                                         if(isset($seo_data['seo_keywords']) && !empty($seo_data['seo_keywords']))
                                                         {
                                                            $oldtags=explode(",",$seo_data['seo_keywords']) ;
                                                         }
                                                         
                                                         ?>
                                                         @foreach($oldtags as $tags)
                                                         @if($tags)
                                                        <li class="tags">{{$tags}}<i class="fa fa-times"></i></li>
                                                        @endif
                                                        @endforeach
                                                        <li class="tags-new">
                                                          <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                                        </li>
                                                      </ul>  
                                                      <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords[]" id="seo_keywords_data"> 
                                                        <!-- <input type="text" class="form-control " -->
                                                        <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                                        <!--required>-->
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                   
                                    
                                  

                            </div>
                            
                        </div>
                    </div>
                </div>
                @else
                
                <div class="row m-0">
                    <div class="col-xl-12">
                        <div class="row">
                            <h2 class="page-title my-3">
                                {{ __('Product Details') }}
                            </h2>
                            <?php $i = 0; ?>
                            <div class='row' id="{{ $i }}">
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required' for='product_status'><strong>{{ __('Categories') }}</strong><span class = "text-danger">*</span></label>
                                        <select name='categories[]' id='assign_category' class='form-control' required>
                                            <option value=''>Select Category</option>
                                            @if($products->category_id=='')
                                            <option value='' selected>{{ __('Uncategorized') }}</option>   
                                            @endif
                                            @foreach ($categories as $category)
                                            <option value='{{ $category->category_id }}' {{ $category->category_id == $products->category_id ? 'selected' : '' }}>{{ __($category->category_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required' for='product_status'><strong>{{ __('Sub Categories') }}</strong><span class = "text-danger">*</span></label>
                                        <select name='sub_category[]' id='assign_subcategory' class='form-control' required>
                                            <option value=''>Select Sub Category</option>
                                            <option value='0' {{  ($products->sub_category_id==0) ? 'selected' : '' }}>No Sub Category Available</option>
                                            @foreach ($sub_categories as $sub_category)
                                            <option value='{{ $sub_category->sub_category_id }}' {{ $sub_category->sub_category_id == $products->sub_category_id ? 'selected' : '' }}>{{ __($sub_category->sub_category_name) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class='col-md-6 col-xl-6 d-none'>
                                    <div class='mb-3'>
                                        <label class='form-label'><strong>{{ __('Product Badge') }}</strong><span class = "text-danger">*</span></label>
                                        <input type='text' value="123" class='form-control' name='badge[]'
                                            placeholder='{{ __(' Product Badge') }}...'
                                            value="{{ $products->badge }}" required>
                                    </div>
                                </div>
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required'><strong>{{ __('Product Image') }}</strong><span class = "text-danger">*</span> <small>(Image should be 800*800) Image Resize :<a href="https://imageresizer.com/" target="blank"><b>Click Here.</b></a></small>
                                    
                                    
                                    
                                    </label>
                                        <div class='input-group mb-2' style="justify-content: space-between;align-items: center;">
                                            <input type='hidden'
                                                class='image{{ $products->id }} media-model form-control'
                                                name='product_image[]' placeholder='{{ __(' Product Image')
                                                }}' value="{{ $products->product_image }}" required readonly>
                                             <label class='mb-0'>
                                                <strong><span id="images_count_uploaded"> {{ count(explode(',',$products->product_image)) }}</span> Images has been uploaded.</strong>
                                             </label>
                                             

                                            <button class='btn btn-primary btn-md' type='button'
                                                onclick="openMedia({{ $products->id }})">{{ __('Choose
                                                image') }}</button>
                                        </div>
                                       
                                        <div id="iamges_preview" class="row">
                                            @foreach(explode(',',$products->product_image) as $images)
                                            <div class='col-md-4 images_preview' id="product_image_id"><img class='w-100'  src='{{asset($images) }}' /> <i class='fa fa-times-circle' onclick="delete_selected_image('{{ $images }}');"></i></div>
                                            
                                            @endforeach
                                        </div>
                                       <small> Max 3 Images Allowed</small>
                                    </div>
                                </div>
                                <div class='col-md-6 col-xl-6'>
                                    <div class = row>
                                        <div class='col-md-6 col-xl-6'>
                                            <div class='mb-3'>
                                                <label class='form-label required'><strong>{{ __('Product Name') }}</strong><span class = "text-danger">*</span></label>
                                                <input type='text' class='form-control' name='product_name[]'
                                                    placeholder='{{ __(' Product Name') }}'
                                                    value="{{ $products->product_name }}" required>
                                            </div>
                                        </div>
                                        <div class='col-md-6 col-xl-6'>
                                            <div class='mb-3'>
                                                <label class='form-label required'><strong>{{ __('SKU') }}</strong></label>
                                                <input type='text' class='form-control' pattern="[a-zA-Z0-9]+" name='sku[]'
                                                    placeholder='{{ __('SKU') }}'
                                                    value="{{ $products->sku }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class='col-md-12 col-xl-12'>
                                    <div class='mb-3'>
                                      <label class='form-label required'><strong>{{ __('Product Description') }}</strong><span class="text-danger">*</span></label>
                                      <textarea class='form-control' maxlength="512" name='product_subtitle[]' id="product_subtitle"
                                        data-bs-toggle='autosize' placeholder='{{ __('Product Description') }}...' required>{!! $products->product_subtitle !!}</textarea>
                                      <div>
                                        <span id="current-title">0</span> / <span id="maximum-title">512</span> {{ __('characters') }}
                                      </div>
                                    </div>
                                  </div>
                                 
                                  @php
                                
                 $plan = DB::table('users')
                           ->where('user_id', Auth::user()->user_id)
                           ->where('status', 1)
                           ->first();
                 $plan_details = DB::table('plans')
                                   ->where('plan_id', $plan->plan_id)
                                   ->first();
                                  
                 $maxVariants = $plan_details->no_of_variants; 
                 
                 @endphp
                              @if (isset($productvariants))
                              {{-- <div class='col-md-6 col-xl-6'>
                                <label class='form-label'><strong>{{ __('Product Variants') }}</strong></label>
                               
                            </div>
                              <div class = "col-md-6 col-xl-6">
                                <div class = "row">
                                    <div class = "col-md-6 col-xl-6">
                                      
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="variants_check" id="variants-no" value="No" @if(isset($products) && $products->variants_check == 'N') checked @endif onchange="toggleVariantFields()">
                                                <label class="form-check-label" for="icon-jpg">No</label>
                                            </div>
                                          
                                        
                                    </div>
                                    <div class = "col-md-6 col-xl-6">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="variants_check" id="variants-yes" value="Yes" @if(isset($products) && $products->variants_check == 'Y') checked @endif onchange="toggleVariantFields()">
                                            <label class="form-check-label" for="icon-video">Yes</label>
                                        </div>
                                    
                                </div>
                                </div>
                            </div> --}}
                           
                                  <div class='col-md-12 col-xl-12' id="variant-fields-container">
                                     
                                          @forelse($variants as $index => $variant)
                                              <div class='row variant-field'>
                                                  @if(isset($attributes) && $attributes->count() > 0)
                                                      @foreach($attributes as $attribute)
                                                          <div class='col-md-3 col-xl-3'>
                                                              <div class='mb-3'>
                                                                  <label class="form-label required"><strong>{{ $attribute->name }}</strong></label>
                                                                  <select name="attributes[{{ $index }}][{{ $attribute->id }}][]" id="attributeSelect_{{ $index }}_{{ $attribute->id }}" class="form-control dynamic-attribute-select">
                                                                    <option value="">Select {{ $attribute->name }}</option>
                                                                      @foreach($attribute->values as $value)
                                                                      @if($value->is_deleted == 0)
                                                                          <option value="{{ $value->id }}" {{ in_array($value->id, json_decode($variant->attributes, true)[$attribute->id] ?? []) ? 'selected' : '' }}>
                                                                              {{ $value->name }}
                                                                          </option>
                                                                        @endif
                                                                      @endforeach
                                                                  </select>
                                                              </div>
                                                          </div>
                                                      @endforeach
                                                  @endif
                                                  @if (isset($inventory))
                                                  <div class='col-md-3 col-xl-3'>
                                                      <div class='mb-3'>
                                                          <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                                          <div class="input-group mb-2">
                                                              <input type='number' class='form-control' name='prices[{{ $index }}]' placeholder='{{ __('Price') }}' value="{{ $variant->price }}">
                                                          </div>
                                                      </div>
                                                  </div>
                                                  <div class='col-md-3 col-xl-3'>
                                                      <div class='mb-3'>
                                                          <label class='form-label'>{{ __('Stock Quantity') }}</label>
                                                          <div class='input-group mb-2'>
                                                              <input type='number' class='form-control stock-input' name='stock_statuses[{{ $index }}]' placeholder='{{ __('Quantity') }}' value="{{ $variant->stock_status }}" oninput="updateTotalStockQuantity()">
                                                              <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                              <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  @else
                                                  <div class='col-md-3 col-xl-3'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'><strong>{{ __('Price') }}</strong></label>
                                                        <div class="input-group mb-2">
                                                            <input type='number' class='form-control' name='prices[{{ $index }}]' placeholder='{{ __('Price') }}' value="{{ $variant->price }}">
                                                            <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                              <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                @endif
                                              </div>
                                          @empty
                                              <!-- If no variants, add empty fields for the first variant -->
                                              <div class='row variant-field'>
                                                  @foreach($attributes as $index => $attribute)
                                                      <div class='col-md-3 col-xl-3'>
                                                          <div class='mb-3'>
                                                              <label class="form-label required">{{ $attribute->name }}</label>
                                                              <select name="attributes[0][{{ $attribute->id }}][]" class="form-control dynamic-attribute-select">
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
                                                  @if (isset($inventory))
                                                  <div class='col-md-3 col-xl-3'>
                                                      <div class='mb-3'>
                                                          <label class='form-label'>{{ __('Price') }}</label>
                                                          <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}'>
                                                      </div>
                                                  </div>
                                                  <div class='col-md-3 col-xl-3'>
                                                      <div class='mb-3'>
                                                          <label class='form-label'>{{ __('Stock Quantity') }}</label>
                                                          <div class='input-group'>
                                                              <input type='number' class='form-control stock-input' name='stock_statuses[0]' placeholder='{{ __('Quantity') }}' value="" oninput="updateTotalStockQuantity()">
                                                              <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                              <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                          </div>
                                                      </div>
                                                  </div>
                                                  @else
                                                  <div class='col-md-3 col-xl-3'>
                                                    <div class='mb-3'>
                                                        <label class='form-label'>{{ __('Price') }}</label>
                                                        <input type='number' class='form-control' name='prices[0]' placeholder='{{ __('Price') }}'>
                                                        <button type="button" id="addVariantBtn" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
                                                        <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
                                                    </div>
                                                </div>
                                                  @endif
                                              </div>
                                          @endforelse
                                            </div>
                              @endif
                             
                                  <div class='col-md-6 col-xl-6'>
                                    <div class='row' >
                                     <div class='col-md-6 col-xl-6'>
                                        <label class='form-label required'>{{ __('Product Documentation
                                            Title') }}</label>
                                        <input type='text' class='form-control' name='vedio_title[]'
                                            min='1' placeholder='{{ __(' Product Documentation
                                            Title') }}'
                                            value="{{ $products->video_title }}" min='1' step='.001'
                                            >
                                         </div>
                                      <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'>{{ __('Product Documentation Icon') }}</label>
                                        <div class="d-flex justify-content-between">
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-png" value="jpg" @if(isset($products) && $products->video_icon == 'jpg') checked @endif>
                                                <label class="form-check-label" for="icon-png">
                                                    <i class="fa fa-image"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-video" value="video" @if(isset($products) && $products->video_icon == 'video') checked @endif>
                                                <label class="form-check-label" for="icon-video">
                                                    <i class="fa fa-play"></i>
                                                </label>
                                            </div>
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="vedio_icon[]" id="icon-document" value="document" @if(isset($products) && $products->video_icon == 'document') checked @endif>
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
                                        <label class='form-label required'>{{ __('Product Documentation
                                            Link') }}</label>
                                        <input type='text' class='form-control' name='vedio_link[]'
                                            min='1' step='.001' value="{{ $products->video_link }}"
                                            placeholder='{{ __(' Product Documentation Link') }}'>
                                         </div>
                                    </div>
                               
                                <div class='col-md-6 col-xl-6'>
                                    <div class='row' >
                                     <div class='col-md-6 col-xl-6'>
                                         <label class='form-label required'>{{ __('No of units') }}<span class = "text-danger">*</span></label>
                                        <input type='number' class='form-control' name='no_of_units[]'
                                            min='1' placeholder='{{ __(' No of units') }}...'
                                            value="{{ $products->no_of_units }}" min='1' 
                                            required>
                                         </div>
                                      <div class='col-md-6 col-xl-6'>
                                          <label class='form-label required'>{{ __('Unit') }}<span class = "text-danger">*</span></label>
                                       
                                            <select name="units[]" class="form-control" >
                                                <option value="">Select Unit</option>
                                                @foreach($units as $units_data)
                                                    <option value="{{$units_data->unit_shortname}}" {{ ($products->units==$units_data->unit_shortname)?"selected":"" }}>{{$units_data->unit_name}}</option>
                                                @endforeach
                                            </select>
                                         </div>
                                    </div>
                                </div>
                                 <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required' for='product_price_status'>{{
                                            __('Price Show/Hide') }}<span class = "text-danger">*</span></label>
                                        <select name='product_price_status[]' id='product_price_status'
                                            class='form-control' required>
                                            <option value='1' {{ ($products->show_price ==1) ? "selected" : "" }}> {{ __('Show Price') }}</option>
                                            <option value='0' {{ ($products->show_price ==0) ? "selected" : "" }}>
                                                {{ __('Hide Price') }}</option>
                                        </select>
                                        <!--<a href='#' class='btn mt-3 btn-danger btn-sm'-->
                                        <!--    onclick='removeProduct({{ $i }})'>{{ __('Remove') }}</a>-->
                                    </div>
                                </div>
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'><label class='form-label required'>{{ __('Regular
                                            Price') }}<span class = "text-danger">*</span></label>
                                        <input type='number' class='form-control' name='regular_price[]'
                                            min='1' placeholder='{{ __(' Regular Price') }}...'
                                            value="{{ $products->regular_price }}" min='1' step='.001'
                                            required>
                                    </div>
                                </div>
                                
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required'>{{ __('Sales Price') }}<span class = "text-danger">*</span></label>
                                        <input type='number' class='form-control' name='sales_price[]'
                                            min='1' step='.001' value="{{ $products->sales_price }}"
                                            placeholder='{{ __(' Sales Price') }}...' required>
                                    </div>
                                </div>
                                <div class='col-md-6 col-xl-6'>
                                    <div class='mb-3'>
                                        <label class='form-label required' for='product_status'>{{
                                            __('Status') }}<span class = "text-danger">*</span></label>
                                        <select name='product_status[]' id='product_status'
                                            class='form-control' required>
                                            <option value='instock' {{ $products->product_status ==
                                                "instock" ? "selected" : "" }}>
                                                {{ __('In Stock') }}</option>
                                            <option value='outstock' {{ $products->product_status ==
                                                "outstock" ? "selected" : "" }}>
                                                {{ __('Out of Stock') }}</option>
                                        </select>
                                        <!--<a href='#' class='btn mt-3 btn-danger btn-sm'-->
                                        <!--    onclick='removeProduct({{ $i }})'>{{ __('Remove') }}</a>-->
                                    </div>
                                </div>
                               
                                @if (isset($productvariants) )
                              
                                @if($variants->isNotEmpty())
                                @if (isset($inventory) )
                                <div class='col-md-6 col-xl-6'>
                                    <div class='row' >
                                     <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                         </div>
                                         <div class='col-md-6 col-xl-6'>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                    <script>
                                        $(document).ready(function() {
                                            $('.toggle-status').on('change', function() {
                                                let productId = $(this).data('product-id');
                                                let $this = $(this); // Reference to the checkbox element
                                                let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                      
                                                $.ajax({
                                                    url: '{{ route("user.toggleProductStatus") }}',
                                                    type: 'POST',
                                                    data: {
                                                        _token: '{{ csrf_token() }}',
                                                        product_id: productId,
                                                        status: status
                                                    },
                                                    success: function(response) {
                                                        if (response.success) {
                                                            // Use response.status to reflect the updated status
                                                            alert('Product status updated successfully!');
                                                        } else {
                                                            alert('Failed to update product status.');
                                                        }
                                                    },
                                                    error: function() {
                                                        alert('Error occurred while updating product status.');
                                                    }
                                                });
                                            });
                                        });
                                      </script>
                                @endif
                              
                                @else
                                @if (isset($inventory) )
                                <div class='col-md-6 col-xl-6'>
                                    <div class='row' >
                                     <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                         </div>
                                         <div class='col-md-6 col-xl-6'>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('.toggle-status').on('change', function() {
                                            let productId = $(this).data('product-id');
                                            let $this = $(this); // Reference to the checkbox element
                                            let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                  
                                            $.ajax({
                                                url: '{{ route("user.toggleProductStatus") }}',
                                                type: 'POST',
                                                data: {
                                                    _token: '{{ csrf_token() }}',
                                                    product_id: productId,
                                                    status: status
                                                },
                                                success: function(response) {
                                                    if (response.success) {
                                                        // Use response.status to reflect the updated status
                                                        alert('Product status updated successfully!');
                                                    } else {
                                                        alert('Failed to update product status.');
                                                    }
                                                },
                                                error: function() {
                                                    alert('Error occurred while updating product status.');
                                                }
                                            });
                                        });
                                    });
                                  </script>
                                @endif
                                @endif
                                @else
                               
                                @if (isset($inventory) )
                                <div class='col-md-6 col-xl-6'>
                                    <div class='row' >
                                     <div class='col-md-6 col-xl-6'>
                                        <label class='form-label'><strong>{{ __('Total Inventory') }}</strong></label>
                                                <input type='number' class='form-control' id='inventory' name='inventory[]' value = "{{$products->quantity}}" placeholder='{{ __('Total Stock Quantity') }}' readonly>
                                         </div>
                                         <div class='col-md-6 col-xl-6'>
                                            <div class="form-check form-switch">
                                                <input class="form-check-input toggle-status" type="checkbox" role="switch" data-product-id="{{ $products->product_id }}" {{ $products->stock == 1 ? 'checked' : '' }}>
                                                <label class="form-check-label"><strong>{{ __('Show Inventory') }}</strong></label>
                                            </div>
                                        </div>
                                        
                                    </div>
                                </div>
                                <script>
                                    $(document).ready(function() {
                                        $('.toggle-status').on('change', function() {
                                            let productId = $(this).data('product-id');
                                            let $this = $(this); // Reference to the checkbox element
                                            let status = $(this).is(':checked') ? 1 : 0; // Check if the toggle is on or off
                                  
                                            $.ajax({
                                                url: '{{ route("user.toggleProductStatus") }}',
                                                type: 'POST',
                                                data: {
                                                    _token: '{{ csrf_token() }}',
                                                    product_id: productId,
                                                    status: status
                                                },
                                                success: function(response) {
                                                    if (response.success) {
                                                        // Use response.status to reflect the updated status
                                                        alert('Product status updated successfully!');
                                                    } else {
                                                        alert('Failed to update product status.');
                                                    }
                                                },
                                                error: function() {
                                                    alert('Error occurred while updating product status.');
                                                }
                                            });
                                        });
                                    });
                                  </script>
                                @endif
                                @endif
                               
                                    
                                    <div id="step-7" class="content col-md-12 col-xl-12">
                                        <?php
                                            $seo_data=json_decode($products->products_tags,true);
                                        ?>
                                        <div class="row">
                                            <div class="col-md-12 col-xl-12 col-sm-12">
                                                <div class="mb-3 bootstrap-tagsinput"> 
                                                <label class="form-label required">{{ __('SEO Tags') }} <small> [After each tag enter Comma ( , )  ]</small></label>
                                                     <ul class="tags-input form-control d-table">
                                                         <?php 
                                                         $oldtags=array();
                                                         if(isset($seo_data['seo_keywords']) && !empty($seo_data['seo_keywords']))
                                                         {
                                                            $oldtags=explode(",",$seo_data['seo_keywords']) ;
                                                         }
                                                         
                                                         ?>
                                                         @foreach($oldtags as $tags)
                                                         @if($tags)
                                                        <li class="tags">{{$tags}}<i class="fa fa-times"></i></li>
                                                        @endif
                                                        @endforeach
                                                        <li class="tags-new">
                                                          <input type="text" name="seo_keywords_data" id="seo_keywords"> 
                                                        </li>
                                                      </ul>  
                                                      <input class="text" type="hidden" value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}" name="seo_keywords[]" id="seo_keywords_data"> 
                                                        <!-- <input type="text" class="form-control " -->
                                                        <!--placeholder="{{ __('SEO Keywords') }}..."  value="{{ isset($seo_data['seo_keywords'])?$seo_data['seo_keywords']:'' }}"-->
                                                        <!--required>-->
                                                </div>
                                            </div>
                                        </div> 
                                    </div>
                                   
                                  
                                </div>
                        
                            </div>
                        </div>
                    </div>
                @endif
                        <div id="more-products" class="row"></div>

                        <div class="col-lg-12 pl-0">
                            <button type="submit" class="btn btn-primary">
                                    {{ __('Submit') }}
                                </button>
                        </div>


                        
                </div>
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
                    @php 
                    
                    $selectedImges = explode(',',$products->product_image);
                    $imgs='';
                    if(is_array($selectedImges) && count($selectedImges) > 0)
                    {
                        $imgs = $selectedImges;
                    }
                    @endphp
                    @foreach ($media as $gallery)
                    <div class="col-6 col-sm-4" >
                        <label class="form-imagecheck mb-2">
                          <input name="form-imagecheck" type="checkbox" value="{{ $gallery->media_url }}" id="{{ $gallery->id }}" onclick="checkforselected({{ $gallery->id }})" class="form-imagecheck-input media_image @if(in_array($gallery->media_url,$imgs)) {{'img-checked'}} @endif"
                          
                          
                          >
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
                <button type="button" class="btn btn-primary"  data-dismiss="modal">{{ __('Select') }}</button>
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
{{-- <script src="https://cdn.ckeditor.com/ckeditor4/4.21.0/standard/ckeditor.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        CKEDITOR.replace('product_subtitle[]', {
            removePlugins: 'image,video',
            toolbar: [
                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike'] },
                { name: 'paragraph', items: ['NumberedList', 'BulletedList', 'Blockquote'] },
                { name: 'links', items: ['Link', 'Unlink'] },
                { name: 'insert', items: ['Table', 'HorizontalRule'] },
                { name: 'tools', items: ['Maximize'] },
                { name: 'editing', items: ['Scayt'] }
            ],
            height: 300,
            removeDialogTabs: 'link:advanced'
        });
    });
</script> --}}
<script src="https://cdn.ckeditor.com/4.20.1/standard/ckeditor.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Parse the existing slot_date in the format that Flatpickr expects (d-m-Y)
        var existingDates = {!! json_encode($products->slot_date ? explode(',', date('d-m-Y', strtotime($products->slot_date))) : []) !!};

        flatpickr("#multi-date-picker", {
            mode: "multiple",           // Enable multiple date selection
            dateFormat: "d-m-Y",        // Format the selected dates for display
            minDate: "today",           // Optional: Restrict past dates
            defaultDate: existingDates, // Pre-select the dates from existing slot_date
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

{{-- <script>
    // Function to update the total inventory based on stock inputs
    function updateInventoryField() {
        let stockInputs = document.querySelectorAll('.stock-input');
        let totalStock = 0;
console.log(stockInputs);
        // Loop through stock inputs and sum their values
        stockInputs.forEach(function(input) {
            let value = parseInt(input.value, 10);
            if (!isNaN(value)) {
                totalStock += value;
            }
        });

        let inventoryInput = document.getElementById('inventory');
        inventoryInput.value = totalStock;

        // Show or hide inventory field based on total stock
        let inventoryField = document.getElementById('inventory_field');
        inventoryField.style.display = totalStock > 0 ? 'block' : 'none';
    }

    // Function to add a new variant field
    function addVariant() {
        console.log('Hello!');
        let container = document.getElementById('variant-fields-container');
        let variantField = document.querySelector('.variant-field');
        let newField = variantField.cloneNode(true); // Clone existing variant field
        let currentIndex = container.querySelectorAll('.variant-field').length; // Get current number of variants

        // Update the name and ID of new field elements
        newField.querySelectorAll('select, input').forEach(function(element) {
            if (element.name) {
                element.name = element.name.replace(/\[\d+\]/, '[' + currentIndex + ']'); // Update index in name
            }
            element.value = ''; // Clear values for the new field
        });

        // Add event listener to the stock input for the new field
        let stockInput = newField.querySelector('.stock-input');
        stockInput.addEventListener('input', updateInventoryField); // Attach real-time update for inventory

        container.appendChild(newField); // Add the new field to the container
        updateInventoryField(); // Update the inventory
    }

    // Function to remove a variant field
    function removeVariant(button) {
        let container = document.getElementById('variant-fields-container');
        let fields = container.querySelectorAll('.variant-field');

        // Only remove if more than one field exists
        if (fields.length > 1) {
            button.closest('.variant-field').remove();
            updateInventoryField(); // Update inventory after removal
        }
    }

    // Ensure inventory is updated on page load
    document.addEventListener("DOMContentLoaded", function() {
        updateInventoryField();

        // Attach event listeners to existing stock inputs
        document.querySelectorAll('.stock-input').forEach(function(input) {
            input.addEventListener('input', updateInventoryField); // Update inventory on input change
        });
    });
</script> --}}

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
        if (appointmentStatus === '1') {
            appointmentFields.style.display = 'block';
            customizedFields.style.display = 'none';
        }
        else if(appointmentStatus === '2')
        {
            appointmentFields.style.display = 'none';
            customizedFields.style.display = 'block';
        }
        else {
            appointmentFields.style.display = 'none';
            customizedFields.style.display = 'none';
        }
    }

    // Check and toggle appointment fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleAppointmentFields();
    });
</script>

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
    removePlugins: 'image,video', // Remove image and video upload features
    toolbar: [
      { name: 'basicstyles', items: ['Bold', 'Italic'] },
      { name: 'paragraph', items: ['NumberedList', 'BulletedList'] },
      { name: 'links', items: ['Link'] },
      { name: 'undo', items: ['Undo', 'Redo'] }
    ],
    removeButtons: 'Image,Video', // Ensure these buttons are removed
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
</script>



<script>

// let variantIndex = {{ $variants->count() ? $variants->count() : 1 }};

// function addVariant() {
//     const variantFieldsContainer = document.getElementById('variant-fields-container');
//     const newVariantRow = document.createElement('div');
//     newVariantRow.classList.add('row', 'variant-field');
    
//     newVariantRow.innerHTML = `
//      @if(isset($attributes) && $attributes->count() > 0)
//         @foreach($attributes as $index => $attribute)
//         <div class='col-md-3 col-xl-3'>
//             <div class='mb-3'>
//                 <label class="form-label required">{{ $attribute->name }}</label>
//                 <select name="attributes[${variantIndex}][{{ $attribute->id }}][]" id="attributeSelect_${variantIndex}_{{ $attribute->id }}" class="form-control dynamic-attribute-select" required>
//                     @foreach($attribute->values as $value)
//                         <option value="{{ $value->id }}">{{ $value->name }}</option>
//                     @endforeach
//                 </select>
//             </div>
//         </div>
//         @endforeach
//         @else
//                                                         <div class='col-md-3 col-xl-3'>
//                                                             <div class='mb-3'>
//                                                                 <a style="float: right;" class = "btn btn-primary" href="{{ route('user.add.attributes', $business_card->card_id) }}">{{ __('Add Attribute') }}</a>
//                                                             </div>
//                                                         </div>
//                                                         <div class='col-md-3 col-xl-3'>
//                                                             <div class='mb-3'>
//                                                                 <a style="float: right;" class = "btn btn-primary" href="{{ route('user.add.values', $business_card->card_id) }}">{{ __('Add Value') }}</a>
//                                                             </div>
//                                                         </div>
//         @endif
        
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
//                     <input type='number' class='form-control' name='stock_statuses[${variantIndex}]' placeholder='{{ __('Quantity') }}' value="" required>
//                     <button type="button" class="btn btn-primary mx-2" onclick="addVariant()">+</button>
//                     <button type="button" class="btn btn-danger" onclick="removeVariant(this)">-</button>
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
// }

let variantIndex = {{ $variants->count() ? $variants->count() : 1 }};
    const maxVariants = {{$maxVariants}}; // Maximum allowed variants

    function addVariant() {
        const variantFieldsContainer = document.getElementById('variant-fields-container');

        if (variantIndex >= maxVariants) {
            alert('You have reached the limit of allowed variants.');
            return;
        }

        const newVariantRow = document.createElement('div');
        newVariantRow.classList.add('row', 'variant-field');
        newVariantRow.innerHTML = `
            <!-- Dynamically create new variant row with attributes, price, and stock quantity -->
            @foreach($attributes as $index => $attribute)
                <div class='col-md-3 col-xl-3'>
                    <div class='mb-3'>
                        <label class="form-label required">{{ $attribute->name }}</label>
                        <select name="attributes[${variantIndex}][{{ $attribute->id }}][]" class="form-control dynamic-attribute-select">
                            <option value="">Select {{ $attribute->name }}</option>

                            @foreach($attribute->values as $value)
                                <option value="{{ $value->id }}">{{ $value->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endforeach
             @if (isset($inventory))
            <div class='col-md-3 col-xl-3'>
                <div class='mb-3'>
                    <label class='form-label'>{{ __('Price') }}</label>
                    <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="">
                </div>
            </div>
            <div class='col-md-3 col-xl-3'>
                <div class='mb-3'>
                    <label class='form-label'>{{ __('Stock Quantity') }}</label>
                    <div class='input-group mb-2'>
                        <input type='number' class='form-control stock-input' name='stock_statuses[${variantIndex}]' placeholder='{{ __('Quantity') }}' value="" oninput="updateTotalStockQuantity()">
                        <button type="button" class="btn btn-danger mx-2" onclick="removeVariant(this)">-</button>
                    </div>
                </div>
            </div>
            @else
             <div class='col-md-3 col-xl-3'>
                <div class='mb-3'>
                    <label class='form-label'>{{ __('Price') }}</label>
                    <input type='number' class='form-control' name='prices[${variantIndex}]' placeholder='{{ __('Price') }}' value="">
                     <button type="button" class="btn btn-danger mx-2" onclick="removeVariant(this)">-</button>
                </div>
            </div>
            @endif
        `;
        variantFieldsContainer.appendChild(newVariantRow);
        variantIndex++;

        if (variantIndex >= maxVariants) {
            document.getElementById('addVariantBtn').style.display = 'none';
        }
    }

    function removeVariant(button) {
        const variantRow = button.closest('.variant-field');
        variantRow.remove();
        variantIndex--;

        updateTotalStockQuantity(); // Update total stock quantity after removal

        if (variantIndex < maxVariants) {
            document.getElementById('addVariantBtn').style.display = 'inline';
        }
    }

    function updateTotalStockQuantity() {
        let totalQuantity = 0;
        document.querySelectorAll('.stock-input').forEach(function (input) {
            let quantity = parseInt(input.value) || 0;
            totalQuantity += quantity;
        });
        document.getElementById('inventory').value = totalQuantity;
    }
    </script>
   
<script>
  $(document).ready(function() {
    // Bind the change event for attribute select elements
    function bindAttributeChangeEvent(attributeSelectElement) {
        $(attributeSelectElement).on('change', function() {
            var attributeId = $(this).val();
            var valueSelect = $(this).closest('.variant-field').find('.value-select');

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

    // Add new variant field
    function addAttribute(button) {
        var container = document.getElementById('variant-fields-container');
        var currentField = button.closest('.variant-field');

        // Clone the current variant field
        var newField = currentField.cloneNode(true);

        // Reset the values of the cloned inputs
        $(newField).find('input').val('');
        $(newField).find('select').val('');

        // Bind the change event for the new attribute select element
        bindAttributeChangeEvent(newField.querySelector('.attribute-select'));

        // Show the remove button in the new field
        $(newField).find('.btn-danger').show();

        // Append the new field to the container
        container.appendChild(newField);

        // Bind the removeAttribute function to the new minus button
        bindRemoveAttributeEvent(newField.querySelector('.btn-danger'));
    }

    // Remove variant field
    function removeAttribute(button) {
        var currentField = button.closest('.variant-field');

        // Only remove the field if there's more than one present
        var variantFields = document.querySelectorAll('.variant-field');
        if (variantFields.length > 1) {
            currentField.remove();
        }
    }

    // Bind the removeAttribute function to remove buttons
    function bindRemoveAttributeEvent(button) {
        $(button).on('click', function() {
            removeAttribute(this);
        });
    }

    // Initial binding of events
    $('.attribute-select').each(function() {
        bindAttributeChangeEvent(this);
    });

    $('.btn-danger').each(function() {
        bindRemoveAttributeEvent(this);
    });

 
    window.addAttribute = addAttribute;
});


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
        $('.image'+currentSelection).val(selectedImages);
    }
</script>
<script type="text/javascript">
    Dropzone.options.dropzone = {
            maxFilesize  : {{ env('SIZE_LIMIT')/1024 }},
            acceptedFiles: ".jpeg,.jpg,.png,.gif",
            init: function() {
            this.on("success", function(file, response) {
                // var uploadImages = `<div class="col-6 col-sm-4">
                //         <label class="form-imagecheck mb-2">
                //           <input name="form-imagecheck" type="checkbox" id="../../images/`+response.image_url+`" class="form-imagecheck-input media_image" onclick="chooseImg(this)">
                //           <span class="form-imagecheck-figure">
                //             <img src="{{asset('/')}}/images/`+response.image_url+`" class="form-imagecheck-image">
                //           </span>
                //         </label>
                //     </div>`;

                // $("#captions").append(uploadImages).html();

                // $('.image'+currentSelection).val(`/images/`+response.image_url);

                // $('#openMediaModel').modal('hide');

                // // Hidden empty
                // $(".empty").hide();
                getnewimages();
            });
        }
        };

        var selectedImages = [];
       
            if($('.image{{ $products->id }}').val()!="")
            selectedImages=$('.image{{ $products->id }}').val().split(",");
        function openMedia(id){
            "use strict";
            
            var currentSelection = id;
          
           
            //$('.image'+currentSelection).val("");
           // $(".media_image").prop("checked", false);
           
            
            // var getImage = $(".media-model").val();
            // if(getImage != '' && getImage.length > 0)
            // {
            //     var splitImage = getImage.split(",");
            //     splitImage.forEach(function(item){
            //         selectedImages.push(item);
            //     })
            // }

            $("#modalImages .media_image").not(":checked").prop("disabled", selectedImages.length >= 3);

            // if($("#modalImages .img-checked").length > 0) {
            //     $("#modalImages .img-checked").prop("checked",true);
            //     if($("#modalImages .img-checked").length >= 3)
            //         $("#modalImages .media_image").not(":checked").prop("disabled", true);
            //     else
            //         $("#modalImages .media_image").not(":checked").prop("disabled", false);
               
            // }
            // else
            // {
            //     $("#modalImages .media_image").not(":checked").prop("disabled", false);
            // }

            $('#openMediaModel').modal('show');
        }

        /*$(".media_image").on( "click", function() {
            var imgUri = $(this).attr('id');
            if (this.checked) {
                if($.inArray(imgUri, selectedImages)) {
                    selectedImages.push(imgUri);
                } else {
                    selectedImages.splice($.inArray(imgUri, selectedImages), 1);
                }
            } else {
                selectedImages.splice($.inArray(imgUri, selectedImages), 1);
            }
            $('.image{{ $products->id }}').val(selectedImages);
        });*/
        
        
         function checkforselected(value_id) {
        var imgUri = $("#" + value_id).attr('value');
        selectedImages = [];
            if($('.image{{ $products->id }}').val()!="")
            selectedImages=$('.image{{ $products->id }}').val().split(",");
        if ($("#" + value_id).is(':checked')) {
    
            if ($.inArray(imgUri, selectedImages) === -1) {
                // Add the image URI to selectedImages array only if it's not already present
                selectedImages.push(imgUri);
            }
    
            if (selectedImages.length > 3) {
                // If more than three images are selected, uncheck the last selected checkbox
                $("#" + value_id).prop("checked", false);
            } else {
                $('.image{{ $products->id }}').val(selectedImages);
            }
        } else {
            // Remove the image URI from selectedImages array
            selectedImages.splice($.inArray(imgUri, selectedImages), 1);
            $('.image{{ $products->id }}').val(selectedImages);
            
        }
        // Disable unchecked checkboxes if the limit is reached
        $("#modalImages .media_image").not(":checked").prop("disabled", selectedImages.length >= 3);
       
        previewimagestodiv();
       
        
    }

        
        
        
        
        
</script>





<script>
    
    
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



function delete_selected_image(imgUri)
{
    selectedImages.splice($.inArray(imgUri, selectedImages), 1);
    $('.image{{ $products->id }}').val(selectedImages);
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


function get_current_images()
{
    var selectedImages_current = [];
            if($('.image{{ $products->id }}').val()!="")
            selectedImages_current=$('.image{{ $products->id }}').val().split(",");
    return selectedImages_current;
}

</script>



{{-- @php
dd($productvariants)
@endphp --}}




@endsection


@endsection