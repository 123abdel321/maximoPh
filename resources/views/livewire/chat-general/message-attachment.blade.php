@if (explode('/', $archivo->tipo_archivo)[0] == 'image')
    <img class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" alt="Imagen" style="max-height: 250px; max-width: 300px; height: auto; margin-bottom: 10px; margin-left: auto;">
@elseif (explode('/', $archivo->tipo_archivo)[0] == 'video')
    <video class="" src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" controls style="max-height: 250px; height: auto; margin-bottom: 10px; margin-left: auto;"></video>
@else
    <iframe src="https://porfaolioerpbucket.nyc3.digitaloceanspaces.com/{{ $archivo->url_archivo }}" style="width: 100%; height: 250px;"></iframe>
@endif
