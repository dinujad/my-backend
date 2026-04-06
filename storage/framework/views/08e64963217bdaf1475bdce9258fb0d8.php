<?php $__env->startSection('title', 'Edit Product: ' . $product->name); ?>

<?php $__env->startSection('content'); ?>
<div x-data="productForm()" class="pb-20">
    <div class="flex items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('admin.products.index')); ?>" class="text-gray-500 hover:text-gray-800"><i class="bi bi-arrow-left"></i></a>
            <h1 class="text-2xl font-bold text-gray-900">Edit Product: <?php echo e($product->name); ?></h1>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(url('/product/' . $product->slug)); ?>" target="_blank" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50 transition flex items-center gap-2">
                <i class="bi bi-box-arrow-up-right"></i> View
            </a>
            <button type="button" @click="$refs.mainForm.submit()" class="px-4 py-2 bg-brand-red text-white rounded-lg text-sm font-medium hover:bg-red-dark transition flex items-center gap-2">
                <i class="bi bi-save"></i> Save Changes
            </button>
        </div>
    </div>

    <?php if($errors->any()): ?>
        <div class="bg-red-50 text-red-600 p-4 rounded-lg mb-6 text-sm border border-red-100">
            <div class="font-medium mb-1">Please fix the following errors:</div>
            <ul class="list-disc pl-5">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    
    <form id="admin-delete-product-form" method="POST" action="<?php echo e(route('admin.products.destroy', $product)); ?>" class="hidden" aria-hidden="true">
        <?php echo csrf_field(); ?>
        <?php echo method_field('DELETE'); ?>
    </form>

    <form x-ref="mainForm" action="<?php echo e(route('admin.products.update', $product)); ?>" method="POST" enctype="multipart/form-data" class="flex flex-col lg:flex-row gap-6">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <input type="hidden" name="attributes_config" :value="JSON.stringify(attributes)">
        
        <!-- MAIN CONTENT: LEFT COLUMN -->
        <div class="flex-1 space-y-6">
            
            <!-- Basic Title & Description -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Product Title *</label>
                    <input type="text" name="name" x-model="title" @input="generateSlug" required
                           class="w-full border border-gray-200 rounded-lg px-3 py-2 focus:border-brand-red outline-none text-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Slug (URL)</label>
                    <div class="flex items-center bg-gray-50 border border-gray-200 rounded-lg px-3 p-1">
                        <span class="text-gray-400 text-sm mr-1"><?php echo e(url('/')); ?>/product/</span>
                        <input type="text" name="slug" x-model="slug" class="bg-transparent border-none outline-none w-full text-sm font-mono text-gray-700">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Short Description</label>
                    <textarea name="short_description" rows="3" x-model="shortDesc"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-brand-red outline-none mb-1"></textarea>
                    <div class="text-xs text-right text-gray-400" x-text="shortDesc ? shortDesc.length + ' / 500' : '0 / 500'"></div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Full Description</label>
                    <textarea name="description" rows="8"
                              class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-brand-red outline-none"><?php echo e(old('description', $product->description)); ?></textarea>
                </div>
            </div>

            <!-- PRODUCT DATA TABS (WooCommerce Style) -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="bg-gray-50 border-b border-gray-100 p-3 flex flex-wrap items-center gap-4">
                    <span class="font-semibold text-gray-800 ml-2">Product Data —</span>
                    <select name="product_type" x-model="productType" class="border border-gray-200 rounded-md px-2 py-1 text-sm bg-white font-medium focus:border-brand-red outline-none">
                        <option value="simple">Simple product</option>
                        <option value="variable">Variable product</option>
                        <option value="digital">Digital product</option>
                        <option value="external">External / Affiliate product</option>
                    </select>
                    
                    <div class="flex items-center gap-3 ml-auto mr-2 text-sm">
                        <label class="flex items-center gap-1.5 cursor-pointer text-gray-600">
                            <input type="checkbox" name="is_virtual" value="1" x-model="isVirtual" class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> Virtual
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-gray-600">
                            <input type="checkbox" name="is_downloadable" value="1" x-model="isDownloadable" class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> Downloadable
                        </label>
                    </div>
                </div>

                <div class="flex flex-col md:flex-row min-h-[400px]">
                    <!-- Tabs Nav -->
                    <div class="w-full md:w-48 bg-gray-50 border-r border-gray-100 flex flex-col pt-2">
                        <button type="button" @click="tab = 'general'" :class="tab === 'general' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-wrench-adjustable me-2 opacity-70"></i> General
                        </button>
                        <button type="button" @click="tab = 'inventory'" :class="tab === 'inventory' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-box-seam me-2 opacity-70"></i> Inventory
                        </button>
                        <button type="button" @click="tab = 'shipping'" x-show="!isVirtual" :class="tab === 'shipping' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-truck me-2 opacity-70"></i> Shipping
                        </button>
                        <button type="button" @click="tab = 'linked'" :class="tab === 'linked' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-link-45deg me-2 opacity-70"></i> Linked Products
                        </button>
                        <button type="button" @click="tab = 'attributes'" :class="tab === 'attributes' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-tag me-2 opacity-70"></i> Attributes
                        </button>
                        <button type="button" @click="tab = 'variations'" x-show="productType === 'variable'" :class="tab === 'variations' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-layers me-2 opacity-70"></i> Variations
                        </button>
                        <button type="button" @click="tab = 'tier_pricing'" :class="tab === 'tier_pricing' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-tags me-2 opacity-70"></i> Tier Pricing
                        </button>
                        <button type="button" @click="tab = 'page_settings'" :class="tab === 'page_settings' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-layout-text-window me-2 opacity-70"></i> Page Layout
                        </button>
                        <button type="button" @click="tab = 'customizations'" :class="tab === 'customizations' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-magic me-2 opacity-70"></i> Customizations
                        </button>
                        <button type="button" @click="tab = 'advanced'" :class="tab === 'advanced' ? 'bg-white border-l-2 border-brand-red text-gray-900 font-medium' : 'text-gray-600 hover:bg-gray-100 border-l-2 border-transparent'" class="px-4 py-3 text-sm text-left transition">
                            <i class="bi bi-gear me-2 opacity-70"></i> Advanced
                        </button>
                    </div>

                    <!-- Tab Contents -->
                    <div class="flex-1 p-5 lg:p-6 bg-white space-y-5">
                        
                        <!-- GENERAL TAB -->
                        <div x-show="tab === 'general'" x-transition.opacity>
                            <div class="space-y-4 max-w-lg">
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Regular price (Rs.)</label>
                                    <input type="number" name="price" step="0.01" x-model.number="price" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Sale price (Rs.)</label>
                                    <input type="number" name="sale_price" step="0.01" x-model.number="salePrice" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                </div>
                                <hr class="border-gray-100 !my-6">
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium text-emerald-600">Cost price (Rs.)</label>
                                    <input type="number" name="cost_price" step="0.01" x-model.number="costPrice" class="w-2/3 border border-emerald-200 rounded-md px-3 py-1.5 focus:border-emerald-500 outline-none">
                                </div>
                                <div class="flex items-center gap-4" x-show="price > 0 && costPrice > 0">
                                    <div class="w-1/3 text-sm text-gray-500 text-right pr-4">Profit Margin</div>
                                    <div class="w-2/3">
                                        <span class="inline-block px-2 py-0.5 bg-emerald-50 text-emerald-700 text-xs font-bold rounded" x-text="calculateMargin() + '%'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- INVENTORY TAB -->
                        <div x-show="tab === 'inventory'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4 max-w-lg">
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">SKU</label>
                                    <input type="text" name="sku" value="<?php echo e(old('sku', $product->sku)); ?>" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 font-mono text-sm focus:border-brand-red outline-none">
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Track stock quantity</label>
                                    <div class="w-2/3 flex items-center">
                                        <input type="checkbox" name="manage_stock" value="1" x-model="manageStock" class="rounded border-gray-300 text-brand-red focus:ring-brand-red">
                                    </div>
                                </div>
                                <div class="flex items-center gap-4" x-show="manageStock">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Quantity</label>
                                    <input type="number" name="stock_quantity" value="<?php echo e(old('stock_quantity', $product->stock_quantity)); ?>" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none" min="0">
                                </div>
                                <div class="flex items-center gap-4" x-show="manageStock">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Low stock threshold</label>
                                    <input type="number" name="low_stock_threshold" value="<?php echo e(old('low_stock_threshold', $product->low_stock_threshold ?? 2)); ?>" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none" min="0">
                                </div>
                                <div class="flex items-center gap-4" x-show="!manageStock">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Stock status</label>
                                    <select name="stock_status" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                        <option value="instock" <?php echo e(old('stock_status', $product->stock_status) === 'instock' ? 'selected' : ''); ?>>In stock</option>
                                        <option value="outofstock" <?php echo e(old('stock_status', $product->stock_status) === 'outofstock' ? 'selected' : ''); ?>>Out of stock</option>
                                        <option value="onbackorder" <?php echo e(old('stock_status', $product->stock_status) === 'onbackorder' ? 'selected' : ''); ?>>On backorder</option>
                                        <option value="preorder" <?php echo e(old('stock_status', $product->stock_status) === 'preorder' ? 'selected' : ''); ?>>Pre-order</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- SHIPPING TAB -->
                        <div x-show="tab === 'shipping'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4 max-w-lg">
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Weight (kg)</label>
                                    <input type="number" step="0.001" name="weight" value="<?php echo e(old('weight', $product->weight)); ?>" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                </div>
                                <div class="flex items-start gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium pt-1">Dimensions (cm) <br><span class="text-xs text-gray-400 font-normal">L x W x H</span></label>
                                    <div class="w-2/3 flex gap-2">
                                        <input type="number" step="0.1" name="length" value="<?php echo e(old('length', $product->length)); ?>" placeholder="Length" class="w-1/3 border border-gray-200 rounded-md px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                        <input type="number" step="0.1" name="width" value="<?php echo e(old('width', $product->width)); ?>" placeholder="Width" class="w-1/3 border border-gray-200 rounded-md px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                        <input type="number" step="0.1" name="height" value="<?php echo e(old('height', $product->height)); ?>" placeholder="Height" class="w-1/3 border border-gray-200 rounded-md px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                    </div>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Shipping class</label>
                                    <select name="shipping_class" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                        <option value="">No shipping class</option>
                                        <option value="bulky" <?php echo e(old('shipping_class', $product->shipping_class) === 'bulky' ? 'selected' : ''); ?>>Bulky</option>
                                        <option value="lightweight" <?php echo e(old('shipping_class', $product->shipping_class) === 'lightweight' ? 'selected' : ''); ?>>Lightweight</option>
                                    </select>
                                </div>
                                <div class="flex items-center gap-4 pt-4">
                                    <label class="w-1/3 text-sm text-gray-600"></label>
                                    <label class="w-2/3 flex items-center gap-2 cursor-pointer text-gray-700 text-sm">
                                        <input type="checkbox" name="is_fragile" value="1" <?php echo e(old('is_fragile', $product->is_fragile) ? 'checked' : ''); ?> class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> 
                                        Fragile (Handle with care)
                                    </label>
                                </div>
                            </div>
                        </div>

                        
                        <?php
                            $allPaymentMethods = \App\Models\PaymentMethod::orderBy('sort_order')->get();
                            $productPaymentIds = old('payment_method_ids', $product->paymentMethods->pluck('id')->toArray());
                        ?>
                        <?php if($allPaymentMethods->isNotEmpty()): ?>
                        <div class="pt-4 border-t border-gray-100 mt-4">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Allowed Payment Methods
                                <span class="text-xs font-normal text-gray-400 ml-1">(Leave all unchecked = allow all active methods)</span>
                            </label>
                            <div class="space-y-2">
                                <?php $__currentLoopData = $allPaymentMethods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pm): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="flex items-center gap-3 cursor-pointer py-1">
                                    <input type="checkbox" name="payment_method_ids[]" value="<?php echo e($pm->id); ?>"
                                           <?php echo e(in_array($pm->id, $productPaymentIds) ? 'checked' : ''); ?>

                                           class="h-4 w-4 rounded border-gray-300 text-brand-red focus:ring-brand-red">
                                    <span class="text-sm text-gray-700"><?php echo e($pm->name); ?></span>
                                    <span class="text-xs rounded-full px-2 py-0.5 <?php echo e($pm->type === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e(ucfirst($pm->type)); ?></span>
                                </label>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- LINKED PRODUCTS TAB -->
                        <div x-show="tab === 'linked'" style="display: none;" x-transition.opacity>
                            <div class="text-gray-500 text-sm text-center py-10 italic">
                                Search boxes for Upsells and Cross-sells will appear here in future updates.
                            </div>
                        </div>

                        <!-- ATTRIBUTES TAB -->
                        <div x-show="tab === 'attributes'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4">
                                <div class="flex gap-2">
                                    <select x-model="newAttributeName" class="flex-1 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none text-sm">
                                        <option value="">Custom product attribute...</option>
                                        <option value="Size">Size</option>
                                        <option value="Color">Color</option>
                                        <option value="Material">Material</option>
                                    </select>
                                    <button type="button" @click="addAttribute" class="px-4 py-1.5 bg-gray-100 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-200 transition border border-gray-200">
                                        Add
                                    </button>
                                </div>
                                
                                <div class="space-y-3 mt-4">
                                    <template x-for="(attr, index) in attributes" :key="index">
                                        <div class="border border-gray-200 rounded-lg bg-gray-50/50 overflow-hidden">
                                            <div class="bg-gray-50 px-4 py-2 flex justify-between items-center border-b border-gray-200">
                                                <div class="font-medium text-sm text-gray-800" x-text="attr.name"></div>
                                                <button type="button" @click="removeAttribute(index)" class="text-red-500 hover:text-red-700 text-sm"><i class="bi bi-x-lg"></i></button>
                                            </div>
                                            <div class="p-4 bg-white flex flex-col md:flex-row gap-4">
                                                <div class="w-full md:w-1/3 space-y-2">
                                                    <label class="text-xs font-semibold uppercase text-gray-500">Name</label>
                                                    <input type="text" x-model="attr.name" class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:border-brand-red outline-none font-bold">
                                                    <div class="flex items-center gap-2 pt-2">
                                                        <input type="checkbox" x-model="attr.used_for_variations" :id="'var_toggle_'+index" class="rounded text-brand-red border-gray-300 focus:ring-brand-red">
                                                        <label :for="'var_toggle_'+index" class="text-xs text-gray-600 cursor-pointer">Used for variations</label>
                                                    </div>
                                                </div>
                                                <div class="w-full md:w-2/3 space-y-2">
                                                    <label class="text-xs font-semibold uppercase text-gray-500">Value(s) <span class="text-gray-400 normal-case ml-1">Separate by pipe (|)</span></label>
                                                    <textarea x-model="attr.valuesStr" rows="2" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none" placeholder="e.g. Small | Medium | Large"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                    
                                    <div x-show="attributes.length === 0" class="text-center py-8 text-sm text-gray-400 border-2 border-dashed border-gray-200 rounded-lg">
                                        No attributes defined yet.
                                    </div>
                                    
                                    <div class="pt-2 text-right">
                                        <button type="button" @click="saveAttributes" class="px-4 py-2 bg-gray-800 text-white rounded-md text-sm font-medium hover:bg-gray-900 transition">Save attributes</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VARIATIONS TAB -->
                        <div x-show="tab === 'variations'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4">
                                <div x-show="!variationsEnabled" class="bg-blue-50 text-blue-800 p-4 rounded-lg text-sm flex gap-3 border border-blue-100">
                                    <i class="bi bi-info-circle-fill text-blue-500 mt-0.5"></i>
                                    <div>Before you can add a variation you need to add some variation attributes on the <strong>Attributes</strong> tab.</div>
                                </div>
                                
                                <div x-show="variationsEnabled" class="flex justify-between items-center bg-gray-50 p-3 rounded-lg border border-gray-200">
                                    <select class="border border-gray-300 rounded px-2 py-1 text-sm bg-white" disabled>
                                        <option>Generate variations from all attributes</option>
                                    </select>
                                    <button type="button" @click="generateVariations" class="px-3 py-1.5 bg-gray-200 hover:bg-gray-300 text-sm font-medium rounded text-gray-700 transition" :disabled="isGeneratingVariations">
                                        <span x-show="!isGeneratingVariations">Generate</span>
                                        <span x-show="isGeneratingVariations">Loading...</span>
                                    </button>
                                </div>

                                <div class="space-y-3 mt-4">
                                    <template x-for="(variation, vIndex) in variations" :key="variation.id || vIndex">
                                        <div class="border border-gray-200 rounded-lg bg-white overflow-hidden shadow-sm" x-data="{ expanded: false }">
                                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 border-b border-gray-100 cursor-pointer" @click="expanded = !expanded">
                                                <div class="flex items-center gap-4">
                                                    <div class="w-8 h-8 rounded bg-gray-200 border border-gray-300 overflow-hidden shrink-0 flex items-center justify-center text-gray-400">
                                                        <i class="bi bi-image"></i>
                                                    </div>
                                                    <span class="font-bold text-gray-700 font-mono text-sm">
                                                        #<span x-text="variation.id ? variation.id : (vIndex + 1)"></span> &nbsp;&mdash;&nbsp; 
                                                        <template x-for="(v, k) in variation.attributes">
                                                            <span><span x-text="v"></span> </span>
                                                        </template>
                                                    </span>
                                                </div>
                                                <div class="flex items-center gap-3">
                                                    <span class="text-xs text-gray-400 font-mono" x-text="variation.price ? 'Rs.'+variation.price : ''"></span>
                                                    <i class="bi text-gray-400" :class="expanded ? 'bi-chevron-up' : 'bi-chevron-down'"></i>
                                                    <button type="button" @click.stop="removeVariation(vIndex)" class="text-red-400 hover:text-red-600 ml-2"><i class="bi bi-trash"></i></button>
                                                </div>
                                            </div>
                                            <div class="p-4 grid grid-cols-1 md:grid-cols-2 gap-4 bg-white" x-show="expanded" x-collapse>
                                                
                                                <!-- Variation Form Fields -->
                                                <input type="hidden" :name="`variations[${vIndex}][id]`" :value="variation.id" />
                                                <input type="hidden" :name="`variations[${vIndex}][sku]`" :value="variation.sku" />
                                                <input type="hidden" :name="`variations[${vIndex}][price]`" :value="variation.price" />
                                                <input type="hidden" :name="`variations[${vIndex}][sale_price]`" :value="variation.sale_price" />
                                                <input type="hidden" :name="`variations[${vIndex}][stock_quantity]`" :value="variation.stock_quantity" />
                                                <input type="hidden" :name="`variations[${vIndex}][stock_status]`" :value="variation.stock_status" />
                                                
                                                <!-- Serialize variation attributes so backend can store them -->
                                                <template x-for="(v, k) in variation.attributes">
                                                    <input type="hidden" :name="`variations[${vIndex}][attributes][${k}]`" :value="v" />
                                                </template>
                                                
                                                <div class="col-span-1 md:col-span-2 flex items-center gap-3 pb-2">
                                                    <label class="flex items-center gap-1.5 text-sm text-gray-600 cursor-pointer">
                                                        <input type="checkbox" checked class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> Enabled
                                                    </label>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">SKU</label>
                                                    <input type="text" x-model="variation.sku" class="w-full border border-gray-200 rounded px-2 py-1 text-sm font-mono focus:border-brand-red outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Stock Status</label>
                                                    <select x-model="variation.stock_status" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                                        <option value="instock">In stock</option>
                                                        <option value="outofstock">Out of stock</option>
                                                        <option value="onbackorder">On backorder</option>
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Regular Price</label>
                                                    <input type="number" step="0.01" x-model.number="variation.price" class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:border-brand-red outline-none">
                                                </div>
                                                <div>
                                                    <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Sale Price</label>
                                                    <input type="number" step="0.01" x-model.number="variation.sale_price" class="w-full border border-gray-200 rounded px-2 py-1 text-sm focus:border-brand-red outline-none">
                                                </div>
                                                <div>
                                                <!-- Image -->
                                                <div>
                                                    <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Image (override)</label>
                                                    <div x-show="variation.has_image" class="mb-2 relative inline-block">
                                                        <img :src="'/' + variation.image_path" class="w-16 h-16 rounded object-cover border border-gray-300">
                                                    </div>
                                                    <input type="file" :name="`variations[${vIndex}][image_file]`" accept="image/*" class="w-full text-xs text-gray-600 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-gray-100 file:text-brand-red hover:file:bg-gray-200">
                                                </div>
                                                
                                                <!-- Variation Tier Pricing -->
                                                <div class="col-span-1 md:col-span-2 pt-2 border-t border-gray-100">
                                                    <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm font-medium mb-3">
                                                        <input type="checkbox" value="1" :name="`variations[${vIndex}][enable_tier_pricing]`" x-model="variation.enable_tier_pricing" class="rounded border-gray-300 text-brand-red focus:ring-brand-red h-4 w-4">
                                                        Enable Variation Tier Pricing
                                                    </label>
                                                    
                                                    <div x-show="variation.enable_tier_pricing" class="space-y-3 mb-2">
                                                        <template x-for="(tier, tIndex) in variation.price_tiers" :key="tIndex">
                                                            <div class="flex items-end gap-2 bg-gray-50 p-2 rounded border border-gray-200">
                                                                <div class="w-1/4">
                                                                    <label class="block text-[10px] text-gray-500 uppercase font-bold mb-1">Min Qty</label>
                                                                    <input type="number" :name="`variations[${vIndex}][price_tiers][${tIndex}][min_qty]`" x-model.number="tier.min_qty" required min="1" class="w-full border border-gray-200 rounded px-1.5 py-1 text-xs focus:border-brand-red outline-none">
                                                                </div>
                                                                <div class="w-1/4">
                                                                    <label class="block text-[10px] text-gray-500 uppercase font-bold mb-1">Max Qty</label>
                                                                    <input type="number" :name="`variations[${vIndex}][price_tiers][${tIndex}][max_qty]`" x-model.number="tier.max_qty" class="w-full border border-gray-200 rounded px-1.5 py-1 text-xs focus:border-brand-red outline-none">
                                                                </div>
                                                                <div class="w-1/4">
                                                                    <label class="block text-[10px] text-gray-500 uppercase font-bold mb-1">Price</label>
                                                                    <input type="number" :name="`variations[${vIndex}][price_tiers][${tIndex}][unit_price]`" x-model.number="tier.unit_price" required step="0.01" class="w-full border border-gray-200 rounded px-1.5 py-1 text-xs focus:border-brand-red outline-none">
                                                                </div>
                                                                <div class="w-1/4 text-center pb-0.5">
                                                                    <button type="button" @click="variation.price_tiers.splice(tIndex, 1)" class="text-red-400 hover:text-red-600 text-sm"><i class="bi bi-trash"></i></button>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <button type="button" @click="if(!variation.price_tiers) variation.price_tiers = []; variation.price_tiers.push({min_qty:'', max_qty:'', unit_price:''})" class="text-xs font-semibold text-brand-red">
                                                            + Add Variation Tier
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- TIER PRICING TAB -->
                        <div x-show="tab === 'tier_pricing'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4">
                                <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm font-medium mb-4">
                                    <input type="checkbox" name="enable_tier_pricing" value="1" x-model="tierPricingEnabled" class="rounded border-gray-300 text-brand-red focus:ring-brand-red h-4 w-4">
                                    Enable Wholesale / Tier Pricing
                                </label>
                                
                                <div x-show="tierPricingEnabled" class="space-y-4">
                                    <template x-for="(tier, index) in priceTiers" :key="index">
                                        <div class="flex items-end gap-3 bg-gray-50 p-3 rounded-lg border border-gray-200">
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Min Qty</label>
                                                <input type="number" :name="`price_tiers[${index}][min_qty]`" x-model.number="tier.min_qty" required min="1" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Max Qty <span class="text-gray-400 normal-case">(optional)</span></label>
                                                <input type="number" :name="`price_tiers[${index}][max_qty]`" x-model.number="tier.max_qty" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                            </div>
                                            <div class="flex-1">
                                                <label class="block text-xs text-gray-500 uppercase font-semibold mb-1">Price / Unit</label>
                                                <input type="number" :name="`price_tiers[${index}][unit_price]`" x-model.number="tier.unit_price" required step="0.01" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                                            </div>
                                            <div class="flex-1">
                                                <button type="button" @click="priceTiers.splice(index, 1)" class="w-full h-[34px] flex items-center justify-center text-red-500 hover:text-red-700 hover:bg-red-50 rounded border border-transparent transition">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                    <button type="button" @click="priceTiers.push({ min_qty: '', max_qty: '', unit_price: '' })" class="text-brand-red hover:underline text-sm font-medium">
                                        <i class="bi bi-plus-circle"></i> Add Tier Rule
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- PAGE SETTINGS TAB -->
                        <div x-show="tab === 'page_settings'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4">
                                <h3 class="font-semibold text-gray-800 border-b pb-2">Single Product Page Display Controls</h3>
                                <p class="text-sm text-gray-500 mb-4">Toggle these options to hide or show specific sections on the frontend product page.</p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <template x-for="setting in [
                                        { key: 'hide_short_desc', label: 'Hide Short Description' },
                                        { key: 'hide_full_desc', label: 'Hide Full Description' },
                                        { key: 'hide_tier_pricing', label: 'Hide Tier Pricing Table' },
                                        { key: 'hide_stock', label: 'Hide Stock Status' },
                                        { key: 'hide_sku', label: 'Hide SKU' },
                                        { key: 'hide_categories', label: 'Hide Categories/Tags' },
                                        { key: 'hide_related', label: 'Hide Related Products' }
                                    ]">
                                        <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm">
                                            <input type="hidden" :name="`page_settings[${setting.key}]`" value="0">
                                            <input type="checkbox" :name="`page_settings[${setting.key}]`" x-model="pageSettings[setting.key]" value="1" class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> 
                                            <span x-text="setting.label"></span>
                                        </label>
                                    </template>
                                </div>

                                <div class="mt-6 pt-4 border-t border-gray-100">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Page Layout Style</label>
                                    <select name="page_settings[layout_style]" x-model="pageSettings.layout_style" class="w-full max-w-sm border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none text-sm bg-white">
                                        <option value="default">Default</option>
                                        <option value="minimal">Minimal / Centered</option>
                                        <option value="woocommerce">Classic WooCommerce</option>
                                    </select>
                                </div>
                                
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Gallery Layout</label>
                                    <select name="page_settings[gallery_layout]" x-model="pageSettings.gallery_layout" class="w-full max-w-sm border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none text-sm bg-white">
                                        <option value="vertical">Vertical Thumbnails (Left)</option>
                                        <option value="horizontal">Horizontal Thumbnails (Bottom)</option>
                                        <option value="slider">Full Slider</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- CUSTOMIZATIONS TAB -->
                        <div x-show="tab === 'customizations'" style="display: none;" x-transition.opacity>
                            <div class="space-y-6">
                                
                                <!-- Global Customization Toggles -->
                                <div class="bg-gray-50 border border-gray-200 p-4 rounded-lg">
                                    <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-200">
                                        <div>
                                            <h3 class="font-semibold text-gray-800">Enable Product Customization</h3>
                                            <p class="text-xs text-gray-500">Allow customers to submit prints, text, and other bespoke details during checkout.</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer" @click.prevent="custSettings.enabled = !custSettings.enabled">
                                            <input type="hidden" name="customization_settings[enabled]" :value="custSettings.enabled ? 1 : 0">
                                            <div class="w-11 h-6 rounded-full transition-colors duration-200" :class="custSettings.enabled ? 'bg-brand-red' : 'bg-gray-300'">
                                                <div class="w-5 h-5 bg-white rounded-full shadow mt-0.5 transition-transform duration-200" :class="custSettings.enabled ? 'translate-x-5 ml-0.5' : 'ml-0.5'"></div>
                                            </div>
                                        </label>
                                    </div>

                                    <div x-show="custSettings.enabled" class="space-y-4" x-transition>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Section Title on Product Page</label>
                                                <input type="text" name="customization_settings[title]" x-model="custSettings.title" class="w-full border border-gray-200 rounded px-3 py-1.5 text-sm focus:border-brand-red outline-none bg-white" placeholder="e.g. Customize your product">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-500 uppercase mb-1">Flat Added Fee (Rs.)</label>
                                                <input type="number" step="0.01" name="customization_settings[flat_fee]" x-model.number="custSettings.flat_fee" class="w-full border border-gray-200 rounded px-3 py-1.5 text-sm focus:border-brand-red outline-none bg-white" min="0">
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center gap-4">
                                            <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm" @click.prevent="custSettings.is_required = !custSettings.is_required">
                                                <input type="hidden" name="customization_settings[is_required]" :value="custSettings.is_required ? 1 : 0">
                                                <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors" :class="custSettings.is_required ? 'bg-brand-red border-brand-red' : 'bg-white border-gray-300'">
                                                    <svg x-show="custSettings.is_required" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 10 10"><path d="M1 5l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </div>
                                                Customers MUST customize this before buying
                                            </label>
                                        </div>

                                        <div class="border-t border-gray-200 pt-4 mt-2 space-y-3">
                                            <label class="flex items-start gap-3 cursor-pointer text-gray-700 text-sm" @click.prevent="custSettings.use_popup = !custSettings.use_popup">
                                                <input type="hidden" name="customization_settings[use_popup]" :value="custSettings.use_popup ? 1 : 0">
                                                <div class="mt-0.5 w-4 h-4 rounded border-2 flex items-center justify-center shrink-0 transition-colors" :class="custSettings.use_popup ? 'bg-brand-red border-brand-red' : 'bg-white border-gray-300'">
                                                    <svg x-show="custSettings.use_popup" class="w-2.5 h-2.5 text-white" fill="none" viewBox="0 0 10 10"><path d="M1 5l3 3 5-5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                                </div>
                                                <span>
                                                    <span class="font-semibold block text-gray-800">Open customization in a popup</span>
                                                    <span class="text-xs text-gray-500 leading-snug block mt-0.5">Product page eke form eka button eken open wenawa. Section title popup eke heading. Flat fee total price eka athulata add wenawa.</span>
                                                </span>
                                            </label>
                                            <div x-show="custSettings.use_popup" x-transition class="pl-7 space-y-1">
                                                <label class="block text-xs font-semibold text-gray-500 uppercase">Popup button label (optional)</label>
                                                <input type="text" name="customization_settings[popup_button_label]" x-model="custSettings.popup_button_label" class="w-full max-w-md border border-gray-200 rounded px-3 py-1.5 text-sm focus:border-brand-red outline-none bg-white" placeholder="e.g. Add artwork & details — +Rs. 500">
                                                <p class="text-[11px] text-gray-400">Empty nam &quot;Section title&quot; eken button text use wenawa.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Repeated Form Builder UI -->
                                <div x-show="custSettings.enabled" x-transition>
                                    <h3 class="font-semibold text-gray-800 mb-2 border-b pb-2">Customization Form Builder</h3>
                                    <p class="text-xs text-gray-500 mb-4">Define the fields you want the customer to fill out (e.g. "Upload Logo", "Front Text").</p>

                                    <div class="space-y-3">
                                        <template x-for="(field, index) in custFields" :key="index">
                                            <div class="flex flex-col gap-2 bg-white border border-gray-200 p-3 rounded-lg shadow-sm">
                                                <div class="flex justify-between items-center mb-2">
                                                    <span class="font-bold text-sm text-gray-700" x-text="'Field #' + (index + 1)"></span>
                                                    <button type="button" @click="custFields.splice(index, 1)" class="text-red-400 hover:text-red-600 text-xs font-semibold"><i class="bi bi-trash"></i> Remove</button>
                                                </div>
                                                <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                                                    <div class="md:col-span-2">
                                                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Field Name / Prompt</label>
                                                        <input type="text" :name="`customization_fields[${index}][label]`" x-model="field.label" required class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none bg-gray-50 focus:bg-white">
                                                    </div>
                                                    <div>
                                                        <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Input Type</label>
                                                        <select :name="`customization_fields[${index}][type]`" x-model="field.type" required class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none bg-gray-50 focus:bg-white">
                                                            <option value="text">Short Text</option>
                                                            <option value="textarea">Long Text / Paragraph</option>
                                                            <option value="select">Dropdown Options</option>
                                                            <option value="radio">Radio Buttons</option>
                                                            <option value="file">File/Image Upload</option>
                                                            <option value="number">Number</option>
                                                        </select>
                                                    </div>
                                                    <div class="flex items-end pb-2">
                                                        <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700">
                                                            <input type="hidden" :name="`customization_fields[${index}][is_required]`" value="0">
                                                            <input type="checkbox" :name="`customization_fields[${index}][is_required]`" value="1" x-model="field.is_required" class="rounded border-gray-300 text-brand-red focus:ring-brand-red"> Required?
                                                        </label>
                                                    </div>
                                                </div>

                                                <!-- Type Specific Options -->
                                                <div x-show="['select', 'radio'].includes(field.type)" class="bg-blue-50/50 p-2 rounded border border-blue-100 mt-2">
                                                    <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Choice Options (comma separated)</label>
                                                    <input type="text" :name="`customization_fields[${index}][options]`" x-model="field.options" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none bg-white" placeholder="e.g. Red, Blue, Green">
                                                </div>
                                                
                                                <div x-show="field.type === 'file'" class="bg-gray-50 p-2 rounded border border-gray-100 mt-2">
                                                    <label class="block text-[10px] uppercase font-bold text-gray-500 mb-1">Accepted File Extensions (comma separated)</label>
                                                    <input type="text" :name="`customization_fields[${index}][accepted_extensions]`" x-model="field.accepted_extensions" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none bg-white" placeholder="e.g. .png, .jpg, .pdf, .ai">
                                                </div>
                                            </div>
                                        </template>

                                        <button type="button" @click="custFields.push({ label: '', type: 'text', is_required: false, options: '', accepted_extensions: '' })" class="w-full border-2 border-dashed border-gray-300 text-gray-500 hover:text-brand-red hover:border-brand-red rounded-lg py-3 text-sm font-semibold transition bg-gray-50 hover:bg-white text-center">
                                            + Add Customization Field
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>

                        <!-- ADVANCED TAB -->
                        <div x-show="tab === 'advanced'" style="display: none;" x-transition.opacity>
                            <div class="space-y-4 max-w-lg">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Purchase note</label>
                                    <textarea name="purchase_note" rows="2" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:border-brand-red outline-none"><?php echo e(old('purchase_note', $product->purchase_note)); ?></textarea>
                                </div>
                                <div class="flex items-center gap-4">
                                    <label class="w-1/3 text-sm text-gray-600 font-medium">Menu order</label>
                                    <input type="number" name="sort_order" value="<?php echo e(old('sort_order', $product->sort_order)); ?>" class="w-2/3 border border-gray-200 rounded-md px-3 py-1.5 focus:border-brand-red outline-none">
                                </div>
                                <div class="pt-2">
                                    <label class="flex items-center gap-2 cursor-pointer text-gray-700 text-sm font-medium">
                                        <input type="checkbox" name="enable_reviews" value="1" <?php echo e(old('enable_reviews', $product->enable_reviews) ? 'checked' : ''); ?> class="rounded border-gray-300 text-brand-red focus:ring-brand-red h-4 w-4">
                                        Enable reviews
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Settings Card -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="font-semibold text-gray-800 flex items-center gap-2">
                    <i class="bi bi-google text-brand-red"></i> SEO Optimization
                </h2>
                
                <!-- Google Snippet Preview -->
                <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 font-sans">
                    <div class="text-xs text-gray-500 mb-2 truncate"><?php echo e(url('/')); ?> › product › <span x-text="slug || 'new-product'"></span></div>
                    <div class="text-xl text-blue-800 hover:underline cursor-pointer truncate" x-text="(seoTitle || title || 'Product Title') + ' - Print Works'"></div>
                    <div class="text-sm text-gray-600 mt-1 line-clamp-2" x-text="seoDesc || shortDesc || 'No meta description provided. Google will try to automatically extract content from the page.'"></div>
                </div>

                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-medium text-gray-700">SEO Title</label>
                            <span class="text-xs" :class="seoTitle && seoTitle.length > 60 ? 'text-red-500' : 'text-gray-400'" x-text="seoTitle ? seoTitle.length + ' / 60' : '0 / 60'"></span>
                        </div>
                        <input type="text" name="seo_title" x-model="seoTitle" maxlength="60"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-brand-red outline-none">
                    </div>
                    <div>
                        <div class="flex justify-between items-end mb-1">
                            <label class="block text-sm font-medium text-gray-700">Meta Description</label>
                            <span class="text-xs" :class="seoDesc && seoDesc.length > 160 ? 'text-red-500' : 'text-gray-400'" x-text="seoDesc ? seoDesc.length + ' / 160' : '0 / 160'"></span>
                        </div>
                        <textarea name="seo_description" x-model="seoDesc" rows="2" maxlength="160"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:border-brand-red outline-none"></textarea>
                    </div>
                </div>
            </div>

        </div>

        <!-- RIGHT SIDEBAR -->
        <div class="w-full lg:w-80 shrink-0 space-y-6">
            
            <!-- Publish Setup -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-4">
                <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Update</h2>
                
                <div class="space-y-3 text-sm text-gray-700">
                    <div class="flex justify-between items-center">
                        <div><i class="bi bi-key me-2 text-gray-400"></i> Status:</div>
                        <select name="status" class="border border-gray-200 rounded px-2 py-1 outline-none focus:border-brand-red font-medium">
                            <option value="published" <?php echo e($product->status === 'published' ? 'selected' : ''); ?>>Published</option>
                            <option value="draft" <?php echo e($product->status === 'draft' ? 'selected' : ''); ?>>Draft</option>
                            <option value="private" <?php echo e($product->status === 'private' ? 'selected' : ''); ?>>Private</option>
                        </select>
                    </div>
                    <div class="flex justify-between items-center">
                        <div><i class="bi bi-eye me-2 text-gray-400"></i> Visibility:</div>
                        <select name="visibility" class="border border-gray-200 rounded px-2 py-1 outline-none focus:border-brand-red font-medium">
                            <option value="shop_search" <?php echo e($product->visibility === 'shop_search' ? 'selected' : ''); ?>>Catalog & Search</option>
                            <option value="shop_only" <?php echo e($product->visibility === 'shop_only' ? 'selected' : ''); ?>>Catalog only</option>
                            <option value="search_only" <?php echo e($product->visibility === 'search_only' ? 'selected' : ''); ?>>Search only</option>
                            <option value="hidden" <?php echo e($product->visibility === 'hidden' ? 'selected' : ''); ?>>Hidden</option>
                        </select>
                    </div>
                    
                    <div class="flex items-center justify-between pt-2">
                        <label class="cursor-pointer flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" <?php echo e($product->is_active ? 'checked' : ''); ?> class="text-brand-red border-gray-300 rounded focus:ring-brand-red">
                            Active
                        </label>
                        <button type="submit"
                            form="admin-delete-product-form"
                            onclick="return confirm('Delete this product?')"
                            class="text-red-500 hover:text-red-700 uppercase font-bold text-xs bg-transparent border-0 cursor-pointer p-0">
                            Delete
                        </button>
                    </div>
                </div>

                <div class="pt-2 border-t border-gray-100">
                    <button type="button" @click="$refs.mainForm.submit()" class="w-full py-2.5 bg-brand-red text-white text-sm font-semibold rounded-lg hover:bg-red-dark transition shadow-sm">
                        Update Product
                    </button>
                </div>
            </div>

            <!-- Categories -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3" x-data="categoryManager()">
                <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Product Categories</h2>
                <div class="max-h-48 overflow-y-auto space-y-1 pr-2 custom-scrollbar text-sm">
                    <template x-for="cat in categories" :key="cat.id">
                        <label class="flex items-center gap-2 cursor-pointer p-1 hover:bg-gray-50 rounded">
                            <input type="radio" :id="'cat_radio_' + cat.id" name="category_id" :value="cat.id" :checked="cat.id == <?php echo e(old('category_id', $product->category_id ?? 'null')); ?>" class="text-brand-red bg-white border-gray-300 focus:ring-brand-red">
                            <span class="text-gray-700" x-text="cat.name"></span>
                        </label>
                    </template>
                </div>

                <div x-show="showAddCategory" class="pt-2 border-t border-gray-100 mt-2" style="display: none;" x-transition>
                    <div class="flex gap-2 mb-1">
                        <input type="text" x-model="newCategoryName" placeholder="Category name" @keydown.enter.prevent="addCategory()" class="w-full border border-gray-200 rounded px-2 py-1.5 text-sm focus:border-brand-red outline-none">
                        <button type="button" @click="addCategory()" :disabled="isAdding" class="bg-gray-100 px-3 py-1.5 rounded text-sm font-medium hover:bg-gray-200 disabled:opacity-50">Add</button>
                    </div>
                </div>
                
                <button type="button" @click="showAddCategory = !showAddCategory" x-show="!showAddCategory" class="text-xs font-medium text-brand-red hover:underline mt-2 inline-block"><i class="bi bi-plus"></i> Add new category</button>
            </div>

            <!-- Media / Image -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-5">
                <div>
                    <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Main Product Image</h2>
                    <div x-data="{ mainPreview: <?php echo json_encode($product->image && str_starts_with($product->image, 'http') ? $product->image : ($product->image ? '/' . ltrim($product->image, '/') : null)) ?>, mainImageError: null }">
                        <input type="file" name="main_image" accept="image/*" 
                            @change="
                                mainImageError = null;
                                const f = $event.target.files[0];
                                if(!f) {
                                    mainPreview = <?php echo json_encode($product->image && str_starts_with($product->image, 'http') ? $product->image : ($product->image ? '/' . ltrim($product->image, '/') : null)) ?>;
                                    return;
                                }
                                const ext = (f.name.split('.').pop() || '').toLowerCase();
                                const allowed = ['jpeg','jpg','png','webp'];
                                if(!allowed.includes(ext) && !(f.type && f.type.startsWith('image/'))) {
                                    mainImageError = 'Main product image must be jpeg/png/jpg/webp only.';
                                    mainPreview = <?php echo json_encode($product->image && str_starts_with($product->image, 'http') ? $product->image : ($product->image ? '/' . ltrim($product->image, '/') : null)) ?>;
                                    $event.target.value = '';
                                    return;
                                }
                                let r = new FileReader();
                                r.onload = e => mainPreview = e.target.result;
                                r.readAsDataURL(f);
                            "
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-red-50 file:text-brand-red hover:file:bg-red-100 mb-2 cursor-pointer mt-2">
                        
                        <div class="mt-2 text-center bg-gray-50 rounded border border-gray-200 p-2 min-h-[150px] flex items-center justify-center">
                            <img :src="mainPreview" class="w-full max-w-[200px] aspect-square object-contain rounded mx-auto" x-show="mainPreview">
                            <span x-show="!mainPreview" class="text-xs text-gray-400">No main image</span>
                        </div>
                        <div x-show="mainImageError" class="mt-2 text-xs text-red-600">
                            <span x-text="mainImageError"></span>
                        </div>
                    </div>
                </div>

                <div>
                    <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Product Gallery</h2>
                    
                    <div class="grid grid-cols-3 gap-2 mb-4 mt-2" x-data="{ existingImages: <?php echo json_encode($product->images->whereNull('product_variation_id')->values(), 15, 512) ?> }">
                        <template x-for="(img, i) in existingImages" :key="img.id || i">
                            <div class="relative group aspect-square rounded border border-gray-200 overflow-hidden bg-gray-50">
                                <img :src="'/' + img.file_path" class="w-full h-full object-cover">
                                <input type="hidden" name="existing_images[]" :value="img.id">
                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition flex items-center justify-center cursor-pointer text-white" @click="existingImages.splice(i, 1)">
                                    <i class="bi bi-trash text-xl text-red-400"></i>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div x-data="{ previews: [], galleryError: null }">
                        <label class="block text-xs font-semibold text-gray-500 mb-2 uppercase">Upload New Gallery Images</label>
                        <input type="file" name="product_images[]" multiple accept="image/*" 
                            @change="
                                galleryError = null;
                                previews = [];
                                const files = Array.from($event.target.files || []);
                                const allowed = ['jpeg','jpg','png','webp'];
                                const invalid = files.filter(f => {
                                    const ext = (f.name.split('.').pop() || '').toLowerCase();
                                    return !allowed.includes(ext) && !(f.type && f.type.startsWith('image/'));
                                });
                                if(invalid.length > 0) {
                                    galleryError = 'Gallery images must be jpeg/png/jpg/webp only.';
                                    $event.target.value = '';
                                    previews = [];
                                    return;
                                }
                                files.forEach(f => {
                                    let r = new FileReader();
                                    r.onload = e => previews.push(e.target.result);
                                    r.readAsDataURL(f);
                                });
                            "
                            class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 mb-2 cursor-pointer mt-2">
                        
                        <div class="grid grid-cols-3 gap-2" x-show="previews.length > 0">
                            <template x-for="p in previews">
                                <img :src="p" class="aspect-square object-cover rounded border border-gray-200 shadow-sm w-full opacity-70">
                            </template>
                        </div>
                        <div x-show="galleryError" class="mt-2 text-xs text-red-600">
                            <span x-text="galleryError"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Material -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Product Material</h2>
                <div class="flex gap-2 mb-1">
                    <input type="text" name="material" value="<?php echo e(old('material', $product->material)); ?>" placeholder="e.g. Acrylic, PVC, Canvas" class="w-full border border-gray-200 rounded px-3 py-1.5 text-sm focus:border-brand-red outline-none">
                </div>
                <div class="text-[11px] text-gray-400">Leave blank if not applicable. Used for frontend filtering.</div>
            </div>

            <!-- Product Tags -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 space-y-3">
                <h2 class="font-semibold text-gray-800 pb-2 border-b border-gray-100">Product Tags</h2>
                <div class="flex gap-2">
                    <input type="text" x-model="newTag" @keydown.enter.prevent="addTag" placeholder="Add new tag" class="w-full border border-gray-200 rounded-md px-3 py-1.5 text-sm focus:border-brand-red outline-none">
                    <button type="button" @click="addTag" class="px-3 bg-gray-100 text-gray-700 rounded-md text-sm border border-gray-200 hover:bg-gray-200 transition">Add</button>
                </div>
                
                <div class="flex flex-wrap gap-2 pt-2">
                    <template x-for="(tag, index) in selectedTags" :key="index">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded bg-gray-100 text-gray-700 border border-gray-200 text-xs font-medium">
                            <span x-text="tag.name"></span>
                            <input type="hidden" name="tags[]" :value="tag.id">
                            <i class="bi bi-x cursor-pointer hover:text-red-500 pt-0.5" @click="removeTag(index)"></i>
                        </span>
                    </template>
                </div>
            </div>

        </div>
    </form>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('categoryManager', () => ({
            showAddCategory: false, 
            newCategoryName: '', 
            isAdding: false,
            categories: <?php echo json_encode($categories, 15, 512) ?>,
            async addCategory() {
                if(!this.newCategoryName.trim()) return;
                this.isAdding = true;
                try {
                    const res = await fetch('<?php echo e(route('admin.products.ajax.categories')); ?>', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
                        body: JSON.stringify({ name: this.newCategoryName })
                    });
                    const data = await res.json();
                    if(data.id) {
                        this.categories.push(data);
                        this.$nextTick(() => {
                            const newRadio = document.getElementById('cat_radio_' + data.id);
                            if(newRadio) newRadio.checked = true;
                        });
                        this.showAddCategory = false;
                        this.newCategoryName = '';
                    }
                } catch(err) {
                    console.error(err);
                } finally {
                    this.isAdding = false;
                }
            }
        }));

        Alpine.data('productForm', () => ({
            title: <?php echo json_encode(old('name', $product->name), 512) ?>,
            slug: <?php echo json_encode(old('slug', $product->slug), 512) ?>,
            shortDesc: <?php echo json_encode(old('short_description', $product->short_description ?? ''), 512) ?>,
            seoTitle: <?php echo json_encode(old('seo_title', $product->seo_data['seo_title'] ?? ''), 512) ?>,
            seoDesc: <?php echo json_encode(old('seo_description', $product->seo_data['seo_description'] ?? ''), 512) ?>,
            price: <?php echo json_encode(old('price', $product->price), 512) ?>,
            salePrice: <?php echo json_encode(old('sale_price', $product->sale_price), 512) ?>,
            costPrice: <?php echo json_encode(old('cost_price', $product->cost_price), 512) ?>,
            manageStock: <?php echo json_encode((bool) old('manage_stock', $product->manage_stock), 512) ?>,
            productType: <?php echo json_encode(old('product_type', $product->product_type ?? 'simple'), 512) ?>,
            isVirtual: <?php echo json_encode((bool) old('is_virtual', $product->is_virtual), 512) ?>,
            isDownloadable: <?php echo json_encode((bool) old('is_downloadable', $product->is_downloadable), 512) ?>,
            tab: 'general',
            
            tierPricingEnabled: <?php echo json_encode($product->priceTiers->whereNull('product_variation_id')->count() > 0, 15, 512) ?>,
            priceTiers: <?php echo json_encode($product->priceTiers->whereNull('product_variation_id')->values()->toArray(), 15, 512) ?>,

            // Page Display Configuration (defaults built in controller; avoids Blade json-directive parse bugs)
            pageSettings: <?php echo json_encode($pageSettingsForJs, 15, 512) ?>,
            
            // Customization
            custSettings: <?php echo json_encode($customizationSettingsForJs, 15, 512) ?>,
            custFields: <?php echo json_encode(old('customization_fields', $customizationFieldsForJs), 512) ?>,
            
            // Attributes & Variations
            attributes: <?php echo json_encode(old('attributes_config', $product->attributes_config ?? []), 512) ?>,
            newAttributeName: '',
            variations: <?php echo json_encode($variationsForJs, 15, 512) ?>,
            isGeneratingVariations: false,
            
            // Tags
            newTag: '',
            selectedTags: <?php echo json_encode($product->tags, 15, 512) ?>,
            
            get variationsEnabled() {
                return this.attributes.some(a => a.used_for_variations && (a.valuesStr || '').trim() !== '');
            },
            
            generateSlug() {
                if(!this.slug) {
                    this.slug = this.title.toLowerCase().replace(/[^\w\s-]/g, '').replace(/[\s_-]+/g, '-').replace(/^-+|-+$/g, '');
                }
            },
            
            calculateMargin() {
                let p = parseFloat(this.price) || 0;
                let c = parseFloat(this.costPrice) || 0;
                if(p > 0 && c > 0 && p > c) {
                    return Math.round(((p - c) / p) * 100);
                }
                return 0;
            },
            
            addAttribute() {
                let name = this.newAttributeName;
                if(!name) { name = prompt('Enter custom attribute name (e.g. Material):'); }
                if(name && !this.attributes.find(a => a.name === name)) {
                    this.attributes.push({ name: name, valuesStr: '', used_for_variations: false });
                }
                this.newAttributeName = '';
            },
            
            removeAttribute(index) {
                this.attributes.splice(index, 1);
            },
            
            saveAttributes() {
                alert('Attributes configured. If you checked "Used for variations", you can now generate variations in the Variations tab.');
                this.tab = 'variations';
            },
            
            async generateVariations() {
                if(!this.variationsEnabled) return;
                
                this.isGeneratingVariations = true;
                
                let validAttributes = this.attributes
                    .filter(a => a.used_for_variations && a.valuesStr.trim().length > 0)
                    .map(a => ({
                        name: a.name,
                        values: a.valuesStr.split('|').map(v => v.trim()).filter(v => v)
                    }));
                
                try {
                    const response = await fetch('<?php echo e(route('admin.products.ajax.generate-variations')); ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                        },
                        body: JSON.stringify({ attributes: validAttributes })
                    });
                    const data = await response.json();
                    
                    if(data.variations) {
                        // Append to existing variations instead of wiping them entirely, 
                        // but for simplicity of this demo we will just merge cautiously or replace.
                        // A true WC system marks existing combos. Here we blindly replace for simplicity.
                        if(confirm('This will wipe out current variations and regenerate them. Continue?')) {
                            this.variations = data.variations;
                        }
                    }
                } catch(e) {
                    console.error('Error generating variations', e);
                    alert('Error generating variations');
                } finally {
                    this.isGeneratingVariations = false;
                }
            },
            
            removeVariation(index) {
                if(confirm('Remove this variation?')) {
                    this.variations.splice(index, 1);
                }
            },
            
            async addTag() {
                let text = this.newTag.trim();
                let tags = text.split(',').map(t => t.trim()).filter(t => t);
                
                for(let t of tags) {
                    if(!this.selectedTags.find(st => st.name.toLowerCase() === t.toLowerCase())) {
                        try {
                            const response = await fetch('<?php echo e(route('admin.products.ajax.tags')); ?>', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
                                },
                                body: JSON.stringify({ name: t })
                            });
                            const tagObj = await response.json();
                            this.selectedTags.push(tagObj);
                        } catch(e) {
                            console.error('Error creating tag', e);
                        }
                    }
                }
                this.newTag = '';
            },
            
            removeTag(index) {
                this.selectedTags.splice(index, 1);
            }
        }));
    });
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\dev\printworks\backend\resources\views\admin\products\edit.blade.php ENDPATH**/ ?>