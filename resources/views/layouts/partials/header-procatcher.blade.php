@inject('request', 'Illuminate\Http\Request')
<!-- Main Header -->
<header class="main-header no-print">
  <a href="{{action('ContactController@index')}}" class="logo">
    <span class="logo-lg">{{ Session::get('business.name') }}</span>
  </a>



<!-- Header Navbar -->
  <nav class="navbar navbar-static-top" role="navigation">


  <!-- Navbar Right Menu -->
    <div class="navbar-custom-menu">
      <select class="pull-left m-8 input-sm" id="change_lang" onchange="changeLang()">
        @foreach(config('constants.langs') as $key => $val)
          <option value="{{$key}}"
                  @if( (empty(request()->lang) && config('app.locale') == $key)
                  || request()->lang == $key)
                  selected
                  @endif
          >
            {{$val['full_name']}}
          </option>
        @endforeach
      </select>


      <div class="m-8 pull-left mt-15 hidden-xs" style="color: #fff;"><strong>{{ @format_date('now') }}</strong></div>

      <ul class="nav navbar-nav">
      <!-- User Account Menu -->
        <li class="dropdown user user-menu">
          <!-- Menu Toggle Button -->
          <a href="#" class="dropdown-toggle" data-toggle="dropdown">
            <!-- The user image in the navbar-->
            <!-- <img src="dist/img/user2-160x160.jpg" class="user-image" alt="User Image"> -->
            <!-- hidden-xs hides the username on small devices so only the image appears. -->
            <span>{{ Auth::User()->first_name }} {{ Auth::User()->last_name }}</span>
          </a>
          <ul class="dropdown-menu">
            <!-- The user image in the menu -->
            <li class="user-header">
              @if(!empty(Session::get('business.logo')))
{{--                <img src="{{ url( 'uploads/business_logos/' . Session::get('business.logo') ) }}" alt="Logo">--}}
                    <img src="{{ env('AWS_IMG_URL').'/uploads/business_logos/' . Session::get('business.logo') }}" alt="Logo">
              @endif
              <p>
                {{ Auth::User()->first_name }} {{ Auth::User()->last_name }}
              </p>
            </li>
            <!-- Menu Body -->
            <!-- Menu Footer-->
            <li class="user-footer">
              <div class="pull-left">
                <a href="{{action('UserController@getProfile')}}" class="btn btn-default btn-flat">@lang('lang_v1.profile')</a>
              </div>
              <div class="pull-right">
                <a href="{{action('Auth\LoginController@logout')}}" class="btn btn-default btn-flat">@lang('lang_v1.sign_out')</a>
              </div>
            </li>
          </ul>
        </li>
        <!-- Control Sidebar Toggle Button -->
      </ul>
    </div>
  </nav>
</header>
<script>
  function changeLang(){
    var link = window.location.href;
    var originLink = link.substring(0, link.indexOf('?') !== -1 ? link.indexOf('?') : link.length);
    window.location.href = originLink + "?lang=" + document.getElementById("change_lang").value;
  }
</script>