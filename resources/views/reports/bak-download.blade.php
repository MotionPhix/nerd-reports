@use('Illuminate\Support\Facades\Vite')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

  <meta charset="UTF-8">

  <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <meta name="csrf-token" content="{{ csrf_token() }}">

  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <title>Weekly Report</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.bunny.net">

  <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet" />

  <link href="https://fonts.bunny.net/css?family=source+serif+pro:400,600,700&display=swap" rel="stylesheet" />

  <style>

    [un-cloak] {

      display: none;

    }

  </style>

  @vite(['public/css/uno.css'])
</head>

<body
  style="
    font-family: 'Inter';
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
    width: 100%
  ">

  <article
    style="
      margin-left: auto;
      margin-right: auto,
      max-width: 48rem,
      padding-left: 2rem;
      padding-right: 2rem;
      padding-top: 3rem;
      padding-bottom: 3rem;
    ">

    <section>

      <p
        style="
          font-size: 2.25rem;
          line-height: 2.25rem;
        "
        class="text-green-500">
        Weekly Report
      </p>

      <hr
        style="
          margin-top: 2.25rem;
          margin-bottom: 2.25rem;
          border-color:rgb(209, 213, 219)
        " />

      <table style="width: 100%; border-spacing: 0;">
        <thead>

          <tr>
            <th></th>

            <th
              style="
                padding-top: 1rem;
                text-align: left;
                font-size: 1.125rem;
                lineheight: 1.75rem;
              ">
              Assigned by
            </th>

            <th
              style="
                padding-top: 1rem;
                padding-left: 10px;
                text-align: left;
                font-size: 1.125rem;
                lineheight: 1.75rem;
              ">
              Status
            </th>
          </tr>

        </thead>

        @foreach ($reportData as $project)

          <tbody
            style="margin-bottom: 5em; width: 100%;">

            <tr>
              <td
                style="
                  width: 60%;
                  border: none rgb(0, 0, 0);
                  border-top-left-radius: 0.5rem;
                  background-color: rgb(229,229,229);
                  padding-left: 0.75rem;
                  padding-top: 1.25rem;
                ">

                <span
                  style="
                    display: block;
                    font-size: 1.25rem;
                    line-height: 1.75rem;
                  ">

                  {{ $project['project_name'] }}

                </span>

                <span
                  style="
                    display: block;
                    font-size: 0.875rem;
                    line-height: 1.25rem;
                  ">
                  For {{ $project['project_contact']?->firm?->name ?? $project['project_contact']->first_name . ' ' . $project['project_contact']->last_name }} | Week {{ $weekNumber }}
                </span>

              </td>

              <td
                style="
                  width: 40%;
                  border-top-right-radius: 0.5rem;
                  background-color: rgb(229,229,229);
                "
                colspan="2">
              </td>
            </tr>

            <tr>
              <td
                style="
                  width: 60%;
                  border: none rgb(0, 0, 0);
                  border-bottom-left-radius: 0.5rem;
                  background-color: rgb(243,244,246);
                ">
                <span><br/></span>
              </td>

              <td
                style="
                  border: none rgb(0, 0, 0);
                  width: 20%;
                  background-color: rgb(243,244,246);
                ">
                <span><br/></span>
              </td>

              <td
                style="
                  width: 20%;
                  border: none rgb(0, 0, 0)
                  border-bottom-right-radius: 0.5rem;
                  background-color: rgb(243,244,246);
                ">
                <span><br/></span>
              </td>
            </tr>

            @foreach ($project['tasks'] as $key => $task)

              <tr>
                <td
                  style="
                    width: 60%;
                    padding: 10px;
                    border-left: none rgb(0, 0, 0);
                    border-right: none rgb(0, 0, 0);
                    border-bottom: none rgb(0, 0, 0);
                    vertical-align: top;
                  "
                  @style([
                    'border-top: 1px rgb(209,213,219)' => !! $key
                  ])>
                  <strong>
                    {{ $task['task_name'] }}
                  </strong>

                  @if (isset($task['task_info']))

                  <div
                    style="
                      font-size: 0.875rem;
                      line-height: 1.25rem;
                      color: rgb(107,114,128);
                    ">
                    {{ $task['task_info'] }}
                  </div>

                  @endif

                  <div
                    style="
                      margin-top: 0.5rem;
                      padding-top: 0.5rem;
                      font-size: 0.875rem;
                      line-height: 1.25rem;
                      color: rgb(156,163,175);
                    ">
                    {{ $task['assigned_at'] . ' | ' . $task['actual_date'] }}
                  </div>
                </td>

                <td
                  style="
                    padding-top: 10px;
                    border-left: none rgb(0, 0, 0);
                    border-right: none rgb(0, 0, 0);
                    border-bottom: none rgb(0, 0, 0);
                    width: 20%;
                    vertical-align: top;
                  "
                  @style([
                    'border-top: 1px rgb(209,23,219)' => !! $key
                  ])>
                  <span>
                    {{ $task['assigned_by'] }}
                  </span>
                </td>

                <td
                  style="
                    width: 20%;
                    padding-top: 10px;
                    padding-left: 10px;
                    border-left: none;
                    border-right: none;
                    border-bottom: none;
                    vertical-align: top;
                  "
                  @style([
                    'border-top: 1px rgb(209,23,219)' => !! $key
                  ])>
                  <span>{{ $task['status'] }}</span>
                </td>
              </tr>

            @endforeach

            <tr>

              <td>

                <div
                  style="
                    margin-top: 2em;
                    margin-bottom: 2em;
                  ">
                </div>

              </td>

            </tr>

          </tbody>

        @endforeach

      </table>

    </section>

    {{-- <section>

      <p>
        <span style="font-family: Helvetica;">
          <img
            src="data:image/png;base64,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" style="width: 130px;" />
        </span>
      </p>

      <p>
        <span style="font-size: 60px; font-family: Helvetica;">Weekly Report</span>
      </p>

      <hr>

      <p>
        <span style="font-family: Helvetica;"><br></span>
      </p>

      <table
        style="width: 100%;">

        <tbody>

          <tr>
            <td
              style="
                width: 100%;
                border: none rgb(0, 0, 0);
              "
              colspan="3">
              <strong
                style="font-family: Helvetica; font-size: 26px;">
                25/04/2024
              </strong>
            </td>
          </tr>

          <tr>
            <td style="width: 64.3685%; border: none rgb(0, 0, 0);"><span style="font-family: Helvetica;"><br></span></td>
            <td style="border: none rgb(0, 0, 0); width: 19.4553%;"><span style="font-family: Helvetica;"><br></span></td>
            <td style="width: 16.1202%; border: none rgb(0, 0, 0);"><span style="font-family: Helvetica;"><br></span></td>
          </tr>

          <tr>
            <td
              style="
                width: 64.3685%;
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
              <span
                style="font-family: Helvetica;">
                Design logo for PIH
              </span>
            </td>

            <td
              style="
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);
                width: 19.4553%;">
              <span style="font-family: Helvetica;">
                Rose Cross
              </span>
            </td>

            <td
              style="
                width: 16.1202%;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
              <span
                style="font-family: Helvetica;">
                Done
              </span>
            </td>

          </tr>

          <tr>
            <td
              style="
                width: 64.3685%;
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
              <span
                style="font-family: Helvetica;">
                Design Flyer for Yonse Bhoo<br>
              </span>
            </td>

            <td
              style="
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);
                width: 19.4553%;">
              <span
                style="font-family: Helvetica;">
                Felix Rook<br>
              </span>
            </td>

            <td
              style="
                width: 16.1202%;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
              <span style="font-family: Helvetica;">
                In Progress
              </span>
            </td>
          </tr>

          <tr>
            <td
              style="
                width: 64.3685%;
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
              <span
                style="font-family: Helvetica;">
                Dump this housde
              </span>
            </td>

            <td
              style="
                padding: 10px;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);
                width: 19.4553%;">
              <span style="font-family: Helvetica;">
                Myself
              </span>
            </td>

            <td
              style="
                width: 16.1202%;
                border-top: 1px solid rgb(0, 0, 0);
                border-left: none rgb(0, 0, 0);
                border-right: none rgb(0, 0, 0);
                border-bottom: none rgb(0, 0, 0);">
                <span style="font-family: Helvetica;">
                  Get out
                </span>
              </td>
          </tr>
        </tbody>
      </table>


    </section> --}}

  </article>

</body>
</html>
