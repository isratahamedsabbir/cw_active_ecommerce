@if(count($product_ids) > 0)
<table class="table table-bordered aiz-table">
    <thead>
      <tr>
        <td width="40%">
            <span>{{translate('Product')}}</span>
        </td>
        <td data-breakpoints="lg" width="15%">
            <span>{{translate('Variant')}}</span>
        </td>
        <td data-breakpoints="lg" width="15%">
            <span>{{translate('Base Price')}}</span>
        </td>
        <td data-breakpoints="lg" width="20%">
            <span>{{translate('Discount')}}</span>
        </td>
        <td data-breakpoints="lg" width="10%">
            <span>{{translate('Discount Type')}}</span>
        </td>
      </tr>
    </thead>
    <tbody>
        @foreach ($product_ids as $key => $id)
            @php
              $product = \App\Models\Product::findOrFail($id);
              $stocks = $product->stocks;
            @endphp
            @foreach ($stocks as $stock)
                @php
                  $flash_deal_product = \App\Models\FlashDealProduct::where('flash_deal_id', $flash_deal_id)
                      ->where('product_id', $product->id)
                      ->where('product_stock_id', $stock->id)
                      ->first();
                  $discount = $flash_deal_product->discount ?? 0;
                  $discount_type = $flash_deal_product->discount_type ?? 'amount';
                @endphp
                <tr>
                    <td>
                      <div class="form-group row">
                          <div class="col-auto">
                              <img src="{{ uploaded_asset($product->thumbnail_img)}}" class="size-60px img-fit" >
                          </div>
                          <div class="col">
                              <span>{{  $product->getTranslation('name')  }}</span>
                          </div>
                      </div>
                    </td>
                    <td>
                        <span>{{ $stock->variant ?: '-' }}</span>
                    </td>
                    <td>
                        <span>{{ $stock->price }}</span>
                    </td>
                    <td>
                        <input type="number" lang="en" name="discount_{{ $id }}_{{ $stock->id }}" value="{{ $discount }}" min="0" step="1" class="form-control" required>
                    </td>
                    <td>
                        <select class="aiz-selectpicker" name="discount_type_{{ $id }}_{{ $stock->id }}">
                            <option value="amount" @if($discount_type == 'amount') selected @endif>{{ translate('Flat') }}</option>
                            <option value="percent" @if($discount_type == 'percent') selected @endif>{{ translate('Percent') }}</option>
                        </select>
                    </td>
                </tr>
            @endforeach
        @endforeach
    </tbody>
</table>
@endif
