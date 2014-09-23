@if (Auth::check())
    <aside>
        <nav>
            <ul>
                <li><a href="{{ url('keyring') }}">Keyrings</a></li>
                <li><a href="{{ url('logout') }}">Logout</a></li>
            </ul>
        </nav>
    </aside>
@endif