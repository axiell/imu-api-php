# Configuration

Before the IMu server can be started it must be configured. IMu server is configured by editing the **etc/imuserver.conf** file in the EMu environment. Each environment will contain an *etc/imuserver.conf.sample* file which can be copied and edited.

The following configuration parameters will almost certainly need to be set:

## main-port

This is the port number that `imuserver` will use to listen for initial connections from the IMu API.

By convention, on a development machine this number is set to `20000` more than the client’s standard EMu port number.

For example, the Art Demo client uses a port number of `20136`. This means that the value of main-port for Art Demo `imuserver` would usually be `40136`.

If this value is not set, it will default to `40000`.

The following settings are less likely to require modification:

## admin-port

This is the number of a secondary port that `imuserver` will also listen on for connections. The difference between this setting and [main-port](#main-port) becomes significant when `imuserver` is running several child processes to handle requests.

A connection on `main-port` may be handled by any of the processes (which is typically what you want), whereas a connection on `admin-port` is guaranteed to be accepted only by the master process which controls all the other processes. This makes this useful for administering the `imuserver` itself, hence the name.

If this value is not set, it defaults to `10000` more than the value of `main-port`

## allow-updates

This setting is used to control whether the server will allow updates to EMu modules via the IMu API. Specifically this setting controls whether calls to the API `Module` class’s `insert`, `update`, `remove` are permitted.

Acceptable values are:

* `no`

    The server will prevent any updates. Any call to any of the three methods will fail. An `IMuException` with an id of **ModulUpdatesNotAllowed** will be thrown.

* `yes`

    The server will not prevent any of these methods being called.

* `authenticated`

    The server will prevent unauthenticated updates.

    By default an `imuserver` runs in unauthenticated mode, accepting API connections without requiring authentication. However, a client may call the API `Session` class’s `login` method to authenticate the client as a particular user.

    This setting specifies that the server will reject calls to this method without the `login` method having been called.

If this value is not set, it defaults to authenticated.

## context-timeout

When IMu client applications create a new connection to the IMu server, the server process allocates a new connection “context”. The context maintains information about the connection, including the `texserver` which it is using and a list of all its active handlers. The context is usually destroyed (including all its handlers) and the associated `texserver` is shutdown when the client application terminates the connection.

However, with stateless applications (such as web pages) the client API may never get a chance to terminate the connection. This can result in connection contexts being maintained by the server when they are not really required. For this reason the server will automatically destroy connection contexts which have not been used for a certain time.

The `context-timeout` parameter specifies how long a connection context can remain unused for before it is automatically discarded by the server. The value is specified as a number followed by a letter:

* `h` (for hours)
* `d` (for days)
* `w` (for weeks)

If this value is not set, it defaults to 3h (three hours).

## data-source

This parameter is used by `imuserver` to determine how to connect to a `texserver` running in the appropriate EMu back-end environment. The value can be:

* A port number or a service name
* A host name and a port number/service name (as host:port)
* An empty string

If this value is not set, it will default to a port number `20000` less than the value used for [main-port](#main-port).

If the value contains a port number or service name, `imuserver` uses it to create a socket connection to a `texserver` process, which is the same way that the EMu client creates its connection. If a host name is not included with port number or service name, the connection will be made to the local machine (`127.0.0.1`).

If the value is empty (i.e. set to an empty string), the IMu Server will use a pipe to connect to `texserver`, rather than a socket.

If a socket connection is used, `imuserver` will try to log in as the user specified in the [user-name](#server-config-user-name) entry. If no user-name is specified, imuserver will try to log in as the user running the imuserver (almost certainly user emu).

> **Important:**
>
> Be aware that for security reasons `imuserver` does not store the user’s password. This means that it must be possible to authenticate the user passed by imuserver to `texserver` without the use of a password. This is achieved by enabling rhosts authentication on the server machine.

## encoding-errors

How to handle “corrupted” UTF-8 data which has been stored in EMu.

A byte with its top bit set (i.e. in the range 128 - 255) may not be part of a valid multi-byte UTF-8 character. This setting controls how to handle an invalid byte before it is returned from the server to an IMu client.

Acceptable values are:

* `replace`

    Any invalid byte is replaced by the standard Unicode “Replacement” character (a black diamond with a white question mark inside).

* `iso-8859-1`

    Any invalid byte is interpreted as a valid ISO-8859-1 character and converted to its UTF-8 equivalent.

This setting is ignored completely unless the EMu `langcode` setting is “utf-8”.

If this value is not set, it defaults to `replace`.

> **Note:**
>
> This setting is really a work-around for a problem which allows invalid UTF-8 characters to be loaded into EMu. You should always use the “replace” option unless you have a specific instance where ISO-8859-1 data has been loaded into a UTF-8 instance of EMu incorrectly.

## handler-timeout

When IMu client applications use functionality in the IMu server, it causes the server to allocate server-side objects which are known as handlers. These handlers are maintained by the server until the client advises the server to destroy them.

However, with stateless applications (such as web pages) the client API may never get a chance to advise the server to destroy the handlers. This can result in handlers being maintained by the server when they are not really required. This can impose a significant memory overhead on the server machine. For this reason the server will automatically destroy handlers which have not been used for a certain time.

The `handler-timeout` parameter specifies how long a handler can remain unused for before it is automatically discarded by the server. The value is specified as a number followed by a letter:

* `h` (for hours)
* `d` (for days)
* `w` (for weeks)

Handlers are described in detail in Understanding the KE IMu Server.

If this value is not set, it defaults to `1h` (one hour).

## language

This is the two-letter code for the language to be used if none is supplied by the API. If this value is not set, it defaults to `en`.

## lookup-list-visibility

This value is used to control which lookup list entries `imuserver` can retrieve when searching eluts. The value can be:

* `internet`

    Only match lookup list entries with the `AdmPublishWebNoPassword` column set to `Yes`.

* `intranet`

    Only match lookup list entries with the `AdmPublishWebPassword` column set to `Yes`.

* `default`

    Use the same setting as the visibility configuration parameter.

* `all`

    Match any lookup list entries.

If this value is not set, it will default to all.

## process-count

This is the number of processes that `imuserver` will create to listen for connections. In environments where there may be a high number of requests it is useful to have several processes listening for connections and handling requests concurrently.

Each process that is started connects to a `texserver`. This means that **each process will use an EMu licence slot**.

## reconnect-port

This is the port number that each server process will start at when trying to allocate a unique port for handling client reconnections.

For example, suppose that process-count is set to 3 and that `reconnect-port` is set to `50000`. When `imuserver` starts, three processes will be created. Each process will independently attempt to allocate a unique port to listen for reconnections. All three processes will first attempt to allocate port `50000`. One will succeed and the other two will fail. The two unsuccessful processes will then move on to try to allocate port `50001`. One will succeed and the other will fail. The unsuccessful process will then allocate port `50002`.

Reconnection to a server is explained in detail in the [Maintaining State](README.md#4-maintaining-state) section of the API documentation.

If this value is not set, it defaults to `5000` more than the value of [main-port](#main-port).

## server-pool

This is the number of `texserver` processes to be run for default access. The `texserver` processes are run using named pipes.

If this value is not set, it defaults to [process-count](#process-count).

## temp-path

This is the path to a directory where `imuserver` creates temporary files.

If the value is not set, it defaults to the same location directory as used by `texserver` for its temporary files.

## trace-file

The is the name of a file used to record server-side tracing information. A value of *STDOUT* causes all information to be written to `imuserver`’s standard output. This is normally what is wanted if `imuserver` is started as an emuload.

If the value is not set, it defaults to *STDOUT*.

## trace-level

This number indicates the amount of server-side tracing information which is generated. A higher number will generate more information. A value of `1` will generate minimal output and a value of `0` will cause no information to be generated at all.

If the value is not set, it defaults to `1`.

> **Note:**
>
> There are not many different levels. The largest value that makes sense is currently `9` - and using `9` generates a **large** amount of information.

## trace-prefix

Specifies the format of text added to the start of each line of tracing information. Certain letters prefixed with a `%` sign have a special meaning and will be substituted in the trace information with other values as follows:

* `%y`: The current year (YYYY).
* `%m`: The current month (MM).
* `%d`: The current day of the month (DD).
* `%D`: A shorthand for “%y-%m-%d”.
* `%H`: The current hour (hh).
* `%M`: The current minute (mm).
* `%S`: The current second (ss).
* `%T`: A shorthand for “%H:%M:%S”.
* `%p`: The `imuserver` <abbr title="Process identifier">PID</abbr>. For example, `16447`.
* `%F`: The file where the trace information was generated. For example, `/home/ke/emu/artdemo/utils/KE/Server/Pool.pm`.
* `%L`: The line of the file where the trace information was generated. For example, `29`.
* `%f`: The fully qualified function in which the trace information was generated. For example, `KE::Server::Pool::new`.
* `%g`: The partially qualified function in which the trace information was generated. For example, `Pool::new`.

So, for example, a `trace-prefix` value of

```
%D %T - %F[%L] - %g:
```

will produce trace messages similar to:

```
2013-03-12 15:07:13 - /home/ke/emu/artdemo/utils/KE/Server/Pool.pm[29] - Pool::new: creating server pool dir /tmp/texpress/imu/40136
```

If no value is specified trace-prefix defaults to:

```
%D %T: %p:
```

## user-name

This is the name of a user that `imuserver` will pass to `texserver` for authentication (see [data-source](#data-source)).

This only applies if `imuserver` is using a socket connection to `texserver`. Be aware that this user must be able to be authenticated by `texserver` without requiring a password.

## visibility

This value is used to control which records `imuserver` can retrieve when searching EMu tables. The value can be:

* `internet`

    Only match records with the AdmPublishWebNoPassword column set to Yes.

* `intranet`

    Only match records with the AdmPublishWebPassword column set to Yes.

* `all`

    Match any records.

If this value is not set, it will default to internet.

## visibility-when-authenticated

This value is used to control which records `imuserver` can retrieve when searching EMu tables after the user has been authenticated (logged in).

The value can be any of those described in the [visibility](#visibility) section.

> **Note:**
>
> Record level security still affects which records can be found by the user. These settings (internet and intranet) are best thought of applying further constraints over and above those of record level security.

If this value is not set, it will default to all.

## watermarking

Ensure that images returned by the server are watermarked. There can be more than one watermarking entry.

The format of a watermarking entry is:

```
<type>:<type-specific parameters>
```

The following values for `<type>` are supported:

* `image`

    An image entry causes the main image to be overlayed with another image.

    > **Note:**
    >
    > This is currently the only form of watermarking supported.

The format for an image entry is as follows:

```
image:<file>:<size>:<position>:<opacity>:<selector>
```

* *\<file\>*

    The path to an image file to be overlayed on the original.

    A relative path will be relative to the `imuserver`’s current directory. This may be a directory such as **~/loads/imu** if the server is started by emuload.

    `$EMUHOME`, `$EMUPATH` and `$HOME` will be expanded to the corresponding values in the server’s environment.

    `~` will be expanded the same as `$HOME`.

    `~emu` will be expanded to the user emu’s home directory.

* *\<size\>*

    The size that the watermark should be set to before being applied to the main image. This can be either:

    * a fixed width and height (e.g. 100x100)
    * a fraction of the size of the main image (e.g. 0.025)
    * a percentage of the size of the main image (e.g. 2.5%)

    If no `<size>` is given then the watermark image is not resized before being applied to the main image.

* *\<position\>*

    Where the watermark should be positioned when being applied to the main image. The permitted values are abbreviated points of the compass:

    * `c` The centre of the main image
    * `n` The top of the image (centred horizontally)
    * `ne` The top right of the image
    * `e` The right of the image (centred vertically)
    * `se` The bottom right of the image
    * `s` The bottom of the image (centred horizontally)
    * `sw` The bottom left of the image
    * `w` The left of the image (centred vertically)
    * `nw` The top left of the image

    If no `<position>` is given it defaults to `c`.

* *\<opacity\>*

    The degree of opaqueness of the watermark image. The value may be either

    * a fraction between 0 (completely transparent) and 1 (completely opaque) (e.g. 0.2)
    * a percentage (e.g. 20%)

    If no `<opacity>` is given it defaults to 20%.

* *\<selector\>*

    The size of image being served that this entry should be applied to. A `<selector>` is a number range and a unit:

    ```
    <number>-<number><unit>
    ```

    `<number>` is a numeric value and may be followed by one of:

    * `k`
    * `ki`
    * `m`
    * `mi`
    * `g`
    * `gi`

    `<unit>` must be either:

    * `px` for pixels
    * `b` for bytes

    e.g. `40k-3mpx` selects images which have between 40,000 and 3,000,000 pixels.

    If no lower range is given the selector matches all images up to (and including) the upper range value.

    e.g. `-20kib` selects images whose file size is up to 20,480 bytes.

    If no upper range is given the selector matches all images at or above the lower range value.

    e.g. `40mi-px` selects images which have 41,943,040 or more pixels.

    If no `<unit>` is given if defaults to `px`.

    If no `<selector>` is given the watermarking rule matches all images.

If there is more than one watermarking entry the image being served is matched against each of the selectors in turn. The rules for the first matching selector are applied to the image.

If there is no match the image is served without having any watermarking applied.

Examples:

1. ```image:~/etc/images/watermark.jpg:3.5%:ne:0.1:3m-px```

    Overlay the image in **~/etc/images/watermark.jpg**, resizing it to overlay at most 3.5% of the image. Place the watermark in the top right of the main image and make its opacity 10%.

    This rule is only applied if the image contains 3 million pixels or more.

1. ```image:/tmp/wm.png:100x100:sw:80%:40k-400kb```

    Overlay the image in **/tmp/wm.png**, resizing it 100x100 pixels before overlaying. Place the watermark in the bottom left of the main image and make its opacity 80%.

    This rule is only applied if the size of the main image file is between 40,000 and 400,000 bytes.

If no `watermarking` value is given, no watermarking is done.
