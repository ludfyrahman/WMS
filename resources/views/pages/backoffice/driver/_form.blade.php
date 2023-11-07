@extends('../../../layouts/' . $layout)

@section('subhead')
    <title>{{ $title }}</title>
@endsection

@section('subcontent')
    <div class="intro-y mt-8 flex items-center">
        <h2 class="mr-auto text-lg font-medium">Form Layout</h2>
    </div>
    @if (session('failed'))
        <x-base.alert class="mb-2 flex items-center" variant="outline-danger">
            <x-base.lucide class="mr-2 h-6 w-6" icon="AlertOctagon" />
            {{ session('failed') }}
            <x-base.alert.dismiss-button class="btn-close" type="button" aria-label="Close">
                <x-base.lucide class="h-4 w-4" icon="X" />
            </x-base.alert.dismiss-button>
        </x-base.alert>
    @endif
    <div class="mt-5 grid grid-cols-12 gap-6">

        <div class="intro-y col-span-12 lg:col-span-12">

            <form action="{{ $route }}" method="post" enctype="multipart/form-data">
                @csrf
                @if ($type != 'create')
                    @method('PUT')
                @endif
                <!-- BEGIN: Form Layout -->
                <div class="intro-y box p-5">
                    <div class="input-form">
                        <x-base.form-label for="crud-form-1">Nama</x-base.form-label>
                        <x-base.form-input class="w-full" id="crud-form-1" type="text" name="name"
                            value="{{ $data->name ?? old('name') }}" placeholder="Input Nama" required />
                        @error('name')
                            <div class="pristine-error text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mt-3 input-form">
                        <x-base.form-label for="crud-form-1">Alamat</x-base.form-label>

                        <x-base.form-textarea class="form-control" id="validation-form-6" name="address"
                            placeholder="Input Alamat" 
                            value="{{ $data->address ?? old('address') }}"></x-base.form-textarea>
                        @error('address')
                            <div class="pristine-error text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="mt-3 input-form">
                        <x-base.form-label for="crud-form-1">No Telp</x-base.form-label>
                        <x-base.form-input class="w-full" id="crud-form-1" type="number" pattern="\d{1,13}" maxlength="13"
                            required value="{{ $data->phone ?? old('phone') }}" name="phone"
                            placeholder="Input No Telp" />
                        @error('phone')
                            <div class="pristine-error text-danger mt-2">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mt-5 text-right">

                        <x-base.button class="mr-1 w-24" type="button" variant="outline-secondary">
                            <a href="{{ route('driver.index') }}" variant="outline-secondary">
                                Cancel
                            </a>
                        </x-base.button>
                        <x-base.button class="w-24" type="submit" variant="primary">
                            Save
                        </x-base.button>
                    </div>
                </div>
                <!-- END: Form Layout -->
            </form>
        </div>
    </div>
@endsection
