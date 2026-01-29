<x-mail::message>
# ¡Nueva respuesta recibida!
* **Detalles de la respuesta:**
* **ID:**{{$answer->id}}
* **Contenido:** {{$answer->body}}


<x-mail::button :url="config('app.url') . '/answers/' . $answer->id">
Ver Respuesta Completa
</x-mail::button>

Gracias,<br>
{{ config('app.name') }}
</x-mail::message>
