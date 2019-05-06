# FAQ

## What ports does IMu use?

IMu based web pages and web services use the IMu API to communicate with imuserver. This communication is via standard TCP sockets. As is usual with TCP-based client/server software, imuserver listens for connections on specific ports and IMu API client programs connect to the server on these ports.

It is quite common for sites to run an imuserver and IMu based clients such as web pages and web services on separate machines. It is also common for there to be a firewall between the client machine (typically a web server) and the imuserver machine. If this is the case, IMu will fail to operate unless the correct holes are put in the firewall. This FAQ describes how to know which holes should be put in the firewall.

When imuserver starts up it listens on a dedicated port. The port number is specified in the server’s configuration file (usually ~/etc/imuserver.conf) by the main-port setting. By convention this port is 20000 more than the port used by the EMu client. For example, artdemo’s EMu client port is 20136 so artdemo’s imuserver uses 40136. (Note that imuserver doesn’t actually know the EMu client port for an environment so you must set at least this value - if you don’t, it will default to 40000 in the same way that the EMu client port defaults to 20000.)

After imuserver has begun listening on the main-port, it may create several child processes to help handle requests. This is controlled by the process-count setting. All these processes will be listening on the same port and when an IMu API program connects to an imuserver, any one of the processes may accept the connection.

This is fine until a stateless program such as a web page wants to connect to the same imuserver process that handled its initial request. For example, a web page may run a search and fetch the first set of matching results. Later a second page of results may be required. The web page cannot connect on the original main-port as this connection may be accepted by one of the other imuserver processes, which know nothing of the original search.

To solve this problem each imuserver allocates its own unique reconnection port. This is described in detail in the [Maintaining State](README.md#4\)-Maintaining-State) section of the IMu API documentation.

The reconnection port numbers used are controlled by the reconnect-port setting in the configuration file. When an imuserver process needs to allocate a new reconnection port it starts from the value specified by the reconnect-port setting. If that port is already in use, it tries the next port and so on until it finds an unused port. IMu API programs then use the imuserver process’s reconnection port to ensure that they are communicating with the correct imuserver process.

By default the reconnection port numbers start at 5000 above the main-port setting. For example, if the main-port is set to 40136, then the reconnect-port value will default to 45136 and reconnection ports will be allocated from 45136 onwards.

What does this mean in terms of holes in a firewall between a sites webserver and the server running EMu? The general rule is that there are process-count + 1 holes required in the firewall: one for the main-port and one for each imuserver process’s own reconnection port. Determining what these port number are is best illustrated by the following examples:

1. 
    The configuration file has a main-port set to 40032, no reconnect-port setting and process-count set to 3.

    Four holes are needed in the firewall:

    * 40032 - the main port, used by all three server processes
    * 45032 - the reconnection port for the first process to request one
    * 45033 - the reconnection port for the second process
    * 45034 - the reconnection port for the third process

1. 
    Similar configuration as above but no process-count defined. The process-count value will default to 1.

    Two holes will be required in the firewall:

    * 40032 - the main port
    * 45032 - the reconnection port

    Here, only a single server process is running and so logically a separate reconnection port is not strictly necessary. However, the current implementation of imuserver does require a reconnection port to be allocated and available for use in this case. This may change in future versions of imuserver.

1. 
    An IT department is setting up to allow IMu to run between a web server and the EMu server. There is a firewall in between the two machines and the IT department wants to put holes in the firewall for the smallest contiguous set of ports possible starting from port 33000.

    First decide how many imuserver processes are required. Suppose it is 5. Six holes will be required from 33000 to 33005.

    Create the following configuration settings:

    ```
    process-count = 5
    main-port = 33000
    reconnect-port = 33001
    ```

One final point is worth making. The specific reconnection port numbers in these examples assume that there are no other programs (either other imuservers running in different environments or completely different programs) using port numbers in the range.

Suppose for example that in the last example an independent program was using port 33002. This port would not be available for the imuserver processes to use as a reconnection port. This would not cause a problem for the imuserver processes themselves; as each process tried to allocate its redirection port it would simply use the next available port, skipping any unavailable ones. This would mean that five server processes would use one of the ports 33001 and 33003-33006. Any IMu API program would handle this fine as well as it would simply connect back to the correct imuserver process on whatever port the imuserver process tells it to. The only issue would arise with the firewall. In this case the holes created would not be adequate as one of the imuserver processes would end up listening for reconnections on port 33006. The API program which tried to connect on this port would fail because it would be blocked by the firewall. A web site configured this way would have what looked like intermittent errors. This could be quite hard to diagnose.

The moral of the story is that when a firewall is involved, always try to ensure that the reconnect-port range does not overlap with ports used by other programs (or other imuservers).

## How does IMu use Texpress licences?

IMu based web pages and web services use the IMu API to communicate with imuserver. The imuserver runs within an EMu client environment. Each imuserver in turn uses one or more texserver processes to get information from EMu modules. Each texserver process used by imuserver will take up one Texpress licence slot.

However, determining exactly how many licence slots will be used by IMu is actually a more difficult than it would at first appear. There are two parts to the answer.

### Default (unauthenticated) access

When imuserver is started (usually by emuload) it reads its configuration file (which is typically in ~/etc/imuserver.conf). The configuration file includes two settings which affect the number of Texpress licences used by imuserver:

* 
    server-pool

    This setting defines how many texservers the imuserver should start to help it service requests. This number directly controls the number of licence slots used.

* 
    process-count

    This setting defines how many imuservers should be started to listen for requests. This does not directly affect the number of Texpress licence used. However, if the configuration file does not include a setting for server-pool, then the server-pool setting defaults to the same number as specified for process-count. This situation is quite common as earlier versions of imuserver did not create server pools: each imuserver process (parent and children) started one dedicated texserver to handle its requests.

    It is important to note that once imuserver has taken a licence slot by starting a texserver as part of the server pool, that licence slot is not released until the imuserver is stopped. That is, the licence slot is not available for use by the EMu client, even if the imuserver is idle.

### Authenticated access

Rather than using the default level of access, an IMu API program may also send a login request to provide access as a specific user. This is common for IMu-based applications which do data capture. When an imuserver receives a login request it starts a dedicated texserver to:

* Check the user name and password

* Provide appropriate access to information (using standard record-level security).

Because a new texserver is started, each login request uses another Texpress licence slot.

Additional licence slots taken by a login request remain in use until either:

* The API program submits a logout request.
* The connection remains idle for a period of time and is timed out. The length of time before the connection is timed out is defined in the configuration file by the context-timeout setting.

In both cases the dedicated texserver started in response to the login request is terminated and, consequently, the licence slot is freed.

#### Examples

1. 
    The **imuserver.conf** file includes a `server-pool` setting of 3 and a `process-count` setting of 5.

    When imuserver starts it first starts a pool of 3 texservers. Once these servers have been started the imuserver creates 4 child processes. This means that there are 5 imuserver processes in total (the parent and the four children) all waiting for connections from IMu API-based clients (typically web pages or web services). Any of these five processes can use any of the three texservers to get data from EMu.

    Number of licence slots used: 3.

1. 
    A `process-count` setting of 2 and no setting for `server-pool`.

    The server-pool setting defaults to 2 (same as process-count). The imuserver starts two texservers and then creates a single child process.

    Number of licence slots used: 2.

1. 
    No `process-count` setting and a `server-pool` setting of 4.

    When a `process-count` setting is not defined in the configuration it defaults to 1.

    This configuration, where the number texservers is greater than the number of imuserver processes, may seem a little odd as usually a single imuserver only processes one request at a time. However, some requests can be run asynchronously. In this case it can make sense to have several texservers available to help service requests.

    Number of licence slots used: 4.

1.
    A `process-count` setting of 2 and no `server-pool` setting. An API program submits a `login` request to authenticate and gain access to EMu data as a registered user.

    Number of licence slots used: 2 to provide default access, 1 further to provide authenticated access. This third slot is used from the time of the `login` request until the same program submits a `logout` request or the connection is timed out.