@extends($templatePathAdmin.'layout')

@section('main')
   <div class="row">
      <div class="col-md-12">
         <div class="card">
                <div class="card-header with-border">
                    <h2 class="card-title">{{ $title_description??'' }}</h2>

                    <div class="card-tools">
                        <div class="btn-group float-right mr-5">
                            <a href="{{ sc_route_admin('admin_store_block.index') }}" class="btn  btn-flat btn-default" title="List"><i class="fa fa-list"></i><span class="hidden-xs"> {{ sc_language_render('admin.back_list') }}</span></a>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ $url_action }}" method="post" accept-charset="UTF-8" class="form-horizontal" id="form-main"  enctype="multipart/form-data">


                    <div class="card-body">
                            <div class="form-group row  {{ $errors->has('name') ? ' text-red' : '' }}">
                                <label for="name" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.name') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                                        </div>
                                        <input type="text" id="name" name="name" value="{{ old('name',$layout['name']??'') }}" class="form-control" placeholder="" />
                                    </div>
                                        @if ($errors->has('name'))
                                            <span class="form-text">
                                                <i class="fa fa-info-circle"></i> {{ $errors->first('name') }}
                                            </span>
                                        @endif
                                </div>
                            </div>
                            <div class="form-group row {{ $errors->has('position') ? ' text-red' : '' }}">
                                <label for="position" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.select_position') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control position select2" style="width: 100%;" name="position" >
                                        <option value=""></option>
                                        @foreach ($layoutPosition as $k => $v)
                                            <option value="{{ $k }}" {{ (old('position',$layout['position']??'') ==$k) ? 'selected':'' }}>{{ sc_language_render($v) }}</option>
                                        @endforeach
                                    </select>
                                        @if ($errors->has('position'))
                                            <span class="form-text">
                                                <i class="fa fa-info-circle"></i> {{ $errors->first('position') }}
                                            </span>
                                        @endif
                                </div>
                                <span style="cursor: pointer;" onclick="imagedemo('https://sc-shared.s3.ap-southeast-1.amazonaws.com/file/block-template.jpg');"><i class="fa fa-question-circle" aria-hidden="true"></i></span>
                            </div>

                            <div class="form-group row {{ $errors->has('page') ? ' text-red' : '' }}">
                                <label for="page" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.select_page') }}</label>
                                <div class="col-sm-8">
                                    <select class="form-control page select2" multiple="multiple" style="width: 100%;" name="page[]" >
                                        <option value=""></option>
                                        @php
                                            $layoutPage = ['*'=> sc_language_render('admin.position_all')] + $layoutPage;
                                            $arrPage = explode(',', $layout['page']??'');
                                        @endphp
                                        @foreach ($layoutPage as $k => $v)
                                            <option value="{{ $k }}" {{ in_array($k,old('page',$arrPage)) ? 'selected':'' }}>{{ sc_language_render($v) }}</option>
                                        @endforeach
                                    </select>
                                        @if ($errors->has('page'))
                                            <span class="form-text">
                                                <i class="fa fa-info-circle"></i> {{ $errors->first('page') }}
                                            </span>
                                        @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('type') ? ' text-red' : '' }}">
                                <label for="type" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.type') }}</label>
                                <div class="col-sm-8">
                            @if ($layout)
                                <label class="radio-inline"><input type="radio" name="type" value="{!! $layout['type'] !!}" checked>{{ $layoutType[$layout['type']]}}</label>
                            @else
                                @foreach ( $layoutType as $key => $type)
                                    <label class="radio-inline"><input type="radio" name="type" value="{!! $key !!}"  {{ (old('type',$layout['type']??'') == $key)?'checked':'' }}>{{ $type }}</label>
                                @endforeach
                            @endif
                                        @if ($errors->has('type'))
                                            <span class="form-text">
                                                <i class="fa fa-info-circle"></i> {{ $errors->first('type') }}
                                            </span>
                                        @endif
                                </div>
                            </div>

                            <div class="form-group row {{ $errors->has('text') ? ' text-red' : '' }}">
                                <label for="text" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.text') }}</label>
                                <div class="col-sm-8">
                                    @php
                                        $dataType = old('type',$layout['type']??'')
                                    @endphp
                                    @if ($dataType =='html')
                                        <textarea name="text" class="form-control text" rows="5" placeholder="Layout text">
                                            {{ old('text',$layout['text']??'') }}
                                        </textarea>
                                    <span class="form-text"><i class="fa fa-info-circle"></i> {{ sc_language_render('admin.store_block.helper_html') }}</span>
                                    @elseif ($dataType =='view')
                                        <select name="text" class="form-control text">
                                            @foreach ($listViewBlock as $view)
                                                <option value="{!! $view !!}" {{ (old('text',$layout['text']??'') == $view)?'selected':'' }} >{{ $view }}</option>
                                            @endforeach
                                        </select>
                                        <span class="form-text"><i class="fa fa-info-circle"></i> {{ sc_language_render('admin.store_block.helper_view',['template' => sc_store('template', session('adminStoreId'))]) }}</span>
                                    @else
                                        <textarea name="text" class="form-control text" rows="5" placeholder="Layout text">
                                            {!! old('text',$layout['text']??'') !!}
                                        </textarea>
                                    @endif


                                    @if ($errors->has('text'))
                                        <span class="form-text">
                                            {{ $errors->first('text') }}
                                        </span>
                                    @endif
                                </div>
                            </div>


                            <div class="form-group row  {{ $errors->has('sort') ? ' text-red' : '' }}">
                                <label for="sort" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.sort') }}</label>
                                <div class="col-sm-8">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                                        </div>
                                        <input type="number" style="width: 100px;"  id="sort" name="sort" value="{{ old()?old('sort'):$layout['sort']??0 }}" class="form-control sort" placeholder="" />
                                    </div>
                                        @if ($errors->has('sort'))
                                            <span class="form-text">
                                                <i class="fa fa-info-circle"></i> {{ $errors->first('sort') }}
                                            </span>
                                        @endif
                                </div>
                            </div>


                            <div class="form-group row ">
                                <label for="status" class="col-sm-2 col-form-label">{{ sc_language_render('admin.store_block.status') }}</label>
                                <div class="col-sm-8">
                                <input class="checkbox" type="checkbox" name="status"  {!! old('status',(empty($layout['status'])?0:1))?'checked':''!!}>

                                </div>
                            </div>
                    </div>



                    <!-- /.card-body -->

                    <div class="card-footer row">
                            @csrf
                        <div class="col-md-2">
                        </div>

                        <div class="col-md-8">
                            <div class="btn-group float-right">
                                <button type="submit" class="btn btn-primary">{{ sc_language_render('action.submit') }}</button>
                            </div>

                            <div class="btn-group float-left">
                                <button type="reset" class="btn btn-warning">{{ sc_language_render('action.reset') }}</button>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-footer -->
                </form>
            </div>
        </div>
    </div>


@endsection

@push('styles')

@endpush

@push('scripts')



<script type="text/javascript">
$(function () {
    $('[name="type"]').change(function(){
    var type = $(this).val();
    var obj = $('[name="text"]');
    obj.next('.form-text').remove();
    if(type =='html'){
       obj.before('<textarea name="text" class="form-control text" rows="5" placeholder="Layout text"></textarea><span class="form-text"><i class="fa fa-info-circle"></i> {{ sc_language_render('admin.store_block.helper_html') }}.</span>');
       obj.remove();
    }else if(type =='view'){
       obj.before('<select name="text" class="form-control text">@foreach ($listViewBlock as $view)<option value="{{ $view }}">{{ $view }}</option>@endforeach</select><span class="form-text"><i class="fa fa-info-circle"></i> {{ sc_language_render('admin.store_block.helper_view',['template' => sc_store('template', session('adminStoreId'))]) }}</span>');
       obj.remove();
    }
    });
});

function imagedemo(image) {
      Swal.fire({
        title: '{{  sc_language_render('admin.template.image_demo') }}',
        text: '',
        imageUrl: image,
        imageWidth: 600,
        imageHeight: 600,
        imageAlt: 'Image demo',
      })
}

</script>

@endpush
