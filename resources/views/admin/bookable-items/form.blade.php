@extends('layouts.panel', ['title' => ($item->exists ? 'Edit' : 'Add').' bookable item'])

@section('content')
<header style="margin-bottom: 32px;">
    <a href="{{ route('admin.bookable-items.index') }}" class="link-claret" style="font-size:14px;">← Back to bookable items</a>
    <h1 style="font-size: 32px; margin: 12px 0 8px;">{{ $item->exists ? 'Edit' : 'Add' }} bookable item</h1>
</header>
@if ($errors->any())<div role="alert" style="background:#FEEBEE;color:#C62828;padding:14px 18px;border-radius:4px;margin-bottom:24px;">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="{{ $item->exists ? route('admin.bookable-items.update', $item) : route('admin.bookable-items.store') }}" style="display:grid; gap:20px;">
    @csrf @if($item->exists) @method('PUT') @endif
    <div class="card-light" style="display:grid; gap:16px;">
        <div><label class="field-label" for="section_id">Section</label><select class="field-light" id="section_id" name="section_id" required>@foreach($sections as $section)<option value="{{ $section->id }}" @selected(old('section_id',$item->section_id)==$section->id)>{{ $section->name }} ({{ $section->access_type }}, {{ $section->type }})</option>@endforeach</select></div>
        <div><label class="field-label" for="name">Name</label><input class="field-light" id="name" name="name" value="{{ old('name',$item->name) }}" required></div>
        <div><label class="field-label" for="description">Description</label><textarea class="field-light" id="description" name="description" rows="3">{{ old('description',$item->description) }}</textarea></div>
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px;">
            <div><label class="field-label" for="price">Price (£)</label><input class="field-light" id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', number_format(($item->price ?? 0)/100,2,'.','')) }}" required></div>
            <div><label class="field-label" for="pricing_unit">Pricing unit</label><select class="field-light" id="pricing_unit" name="pricing_unit">@foreach(['flat'=>'Flat','hour'=>'Hour','day'=>'Day'] as $v=>$l)<option value="{{ $v }}" @selected(old('pricing_unit',$item->pricing_unit)===$v)>{{ $l }}</option>@endforeach</select></div>
            <div><label class="field-label" for="capacity">Capacity</label><input class="field-light" id="capacity" name="capacity" type="number" min="0" value="{{ old('capacity',$item->capacity) }}"></div>
        </div>
        <label style="display:flex; gap:10px; align-items:center;"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $item->exists ? $item->is_active : true))> Active</label>
    </div>
    <div><button type="submit" class="btn btn-navy">Save item</button></div>
</form>
@endsection
