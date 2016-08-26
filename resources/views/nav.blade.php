<nav class="navbar navbar-default navbar-fixed-top">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
        <a class="navbar-brand" href="#">Fancy Football</a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav">
                <li {{$activelink == 'home' ? 'class=active' : ''}}><a href="/">Home</a></li>
                <li {{$activelink == 'about' ? 'class=active' : ''}}><a href="/about">About</a></li>
                <li {{$activelink == 'contact' ? 'class=active' : ''}}><a href="/contact">Contact</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right">
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">{{$user->name}} <span class="caret"></span></a>
                    <ul class="dropdown-menu">
                    <li><a href="#" onclick="window.open('/auth/logout','auth','width=700,height=600');return false;">Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>