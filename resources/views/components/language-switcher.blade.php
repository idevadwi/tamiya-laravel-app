<div class="dropdown d-inline-block">
    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <i class="fas fa-globe"></i>
        @if(app()->getLocale() == 'en')
            English
        @elseif(app()->getLocale() == 'id')
            Bahasa Indonesia
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
        <form action="{{ route('language.switch') }}" method="POST" class="px-2">
            @csrf
            <button type="submit" name="locale" value="en" class="dropdown-item {{ app()->getLocale() == 'en' ? 'active' : '' }}">
                <i class="fas fa-flag-usa"></i> English
            </button>
            <button type="submit" name="locale" value="id" class="dropdown-item {{ app()->getLocale() == 'id' ? 'active' : '' }}">
                <i class="fas fa-flag"></i> Bahasa Indonesia
            </button>
        </form>
    </div>
</div>

