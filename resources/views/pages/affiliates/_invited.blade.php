@if (count($errors->all()) > 0)
<div class="error-container">
    <p> {{ Session::get('invitationException') }} </p>
    @foreach ($errors->all() as $error)
    <p> {{ $error }} </p>
    @endforeach
</div>
@endif

@if (Session::has('invitationException'))
<div class="exception-container">
    <p> {{ Session::get('invitationException') }} </p>
</div>
@endif

@if (isset($guestList))
    @if (count($guestList))
    <p>Success, the following affiliates have been invited to the party.</p>
    <div class="table-container">
        <table>
            <tr>
                <th>Affiliate ID</th>
                <th>Name</th>
                <th>Latitude</th>
                <th>Longitude</th>
            </tr>
            @foreach ($guestList as $entry)
            <tr>
                <td>{{ $entry->affiliate_id }}</td>
                <td>{{ $entry->name }}</td>
                <td>{{ $entry->latitude }}</td>
                <td>{{ $entry->longitude }}</td>
            </tr>
            @endforeach
        </table>
    </div>
    @else
    <p>Sadly, we couldn't find any eligible affiliates to invite. :-(</p>
    @endif
@endif