@extends($templatePathAdmin.'layout')

@section('main')
   <div class="row">
      <div class="col-md-12">
        <div class="card card-primary card-outline card-outline-tabs">
          <div class="card-header p-0 border-bottom-0">
            <ul class="nav nav-tabs" id="custom-tabs-four-tab" role="tablist">
              <li class="nav-item">
                <a class="nav-link active" href="#"  aria-controls="custom-tabs-four-home" aria-selected="true">{{ sc_language_render('admin.template.local') }}</a>
              </li>
              @if (config('admin.settings.api_template'))
              <li class="nav-item">
                <a class="nav-link" href="{{ sc_route_admin('admin_template_online.index') }}" >{{ sc_language_render('admin.template.online') }}</a>
              </li>
              @endif
              <li class="nav-item">
                <a class="nav-link" target=_new  href="{{ sc_route_admin('admin_template.import') }}" ><span><i class="fas fa-save"></i> {{ sc_language_render('admin.plugin.import_data', ['data' => 'template']) }}</span></a>
              </li>
              <li class="btn-group float-right m-2">
                {!! sc_language_render('admin.template.template_more') !!}
              </li>
            </ul>
          </div>

          <div class="card-body" id="pjax-container">
            <div class="tab-content" id="custom-tabs-four-tabContent">
              <div class="table-responsive">
              <table class="table table-hover text-nowrap table-bordered">
                <thead>
                  <tr>
                    <th>{{ sc_language_render('admin.template.image') }}</th>
                    <th>{{ sc_language_render('admin.template.name') }}</th>
                    <th>{{ sc_language_render('admin.template.code') }}</th>
                    <th>{{ sc_language_render('admin.template.image_demo') }}</th>
                    <th>{{ sc_language_render('admin.template.auth') }}</th>
                    <th>{{ sc_language_render('admin.template.website') }}</th>
                    <th>{{ sc_language_render('admin.template.version') }}</th>
                    <th>{{ sc_language_render('admin.template.action') }}</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($templates as $key => $template)
                  <tr>
                    @php
                        $pathImage = $template['config']['image'] ?? '';
                    @endphp
                   <td>{!!sc_image_render(sc_file('templates/'.$key.'/'.$pathImage)??'','50px','', $template['config']['name']??'')!!}</td>
                   <td>{{ $template['config']['name']??'' }}</td>
                   <td>{{ $key??'' }}</td>
                   <td class="pointer" onclick="imagedemo('{{ sc_file('templates/'.$key.'/images/demo.jpg') }}')"><a>{{ sc_language_render('admin.template.click_here') }}</a></td>
                   <td>{{ $template['config']['auth']??'' }}</td>
                   <td><a href="{{ $template['config']['website']??'' }}" target=_new><i class="fa fa-link" aria-hidden="true"></i>Link</a></td>
                   <td>{{ $template['config']['version']??'' }}</td>
                    <td>
                      @if (!in_array($key, $templatesUsed))
                        <span onClick="removeTemplate($(this), '{{ $key }}');" title="{{ sc_language_render('admin.template.remove') }}" class="btn btn-flat btn-danger btn-sm"><i class="fa fa-trash"></i></span>
                      @else
                      <span class="tn btn-flat btn-success btn-sm" title="{{ sc_language_render('admin.template.used') }}"><i class="fa fa-check" aria-hidden="true"></i></span>
                      @endif
                    </td>
                  </tr>
                @endforeach
                </tbody>
              </table>
              </div>
            </div>
          </div>
          <!-- /.card -->
        </div>
        </div>
    </div>

@endsection


@push('styles')

@endpush

@push('scripts')


    {{-- //End pjax --}}
    <script type="text/javascript">
      function removeTemplate(obj,key) {

        Swal.fire({
          title: '{{ sc_language_render('action.action_confirm') }}',
          text: '{{ sc_language_render('action.action_confirm_warning') }}',
          type: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#3085d6',
          cancelButtonColor: '#d33',
          confirmButtonText: '{{ sc_language_render('action.confirm_yes') }}',
        }).then((result) => {
          if (result.value) {
              $('#loading').show()
              obj.button('loading');
              $.ajax({
                type: 'POST',
                dataType:'json',
                url: '{{ sc_route_admin('admin_template.remove') }}',
                data: {
                  "_token": "{{ csrf_token() }}",
                  "key":key,
                },
                success: function (response) {
                  console.log(response);
                if(parseInt(response.error) ==0){
                  alertMsg('success', response.msg);
                location.reload();
                }else{
                  alertMsg('error', response.msg);
                }
                $('#loading').hide();
                obj.button('reset');
                }
              });
          }
        })
    }

function imagedemo(image) {
  Swal.fire({
    title: '{{  sc_language_render('admin.template.image_demo') }}',
    text: '',
    imageUrl: image,
    imageWidth: 800,
    imageHeight: 800,
    imageAlt: 'Image demo',
  })
}
    
  </script>
@endpush
