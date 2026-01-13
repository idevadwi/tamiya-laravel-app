<li class="nav-item dropdown">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
        <i class="fas fa-globe"></i>
        <span class="d-none d-md-inline ml-1">
            @if(app()->getLocale() == 'en')
                EN
            @elseif(app()->getLocale() == 'id')
                ID
            @endif
        </span>
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <form action="{{ route('language.switch') }}" method="POST" id="locale-en-form" class="d-none">
            @csrf
            <input type="hidden" name="locale" value="en">
        </form>
        <form action="{{ route('language.switch') }}" method="POST" id="locale-id-form" class="d-none">
            @csrf
            <input type="hidden" name="locale" value="id">
        </form>
        
        <a href="#" class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}" 
           onclick="event.preventDefault(); document.getElementById('locale-en-form').submit();">
            <i class="fas fa-flag-usa mr-2"></i> English
        </a>
        <a href="#" class="dropdown-item {{ app()->getLocale() == 'id' ? 'active' : '' }}"
           onclick="event.preventDefault(); document.getElementById('locale-id-form').submit();">
            <i class="fas fa-flag mr-2"></i> Bahasa Indonesia
        </a>
    </div>
</li>

