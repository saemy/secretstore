@if (Auth::check())
    <aside>
        <ul>
            <li><a href="{{ url('keyring') }}">Keyrings</a></li>
            <li><a href="{{ url('login') }}">Logout</a></li>
        </ul>
    </aside>
@endif