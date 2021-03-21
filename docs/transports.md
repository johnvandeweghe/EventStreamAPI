#Transports / Subscriptions
Transports are used to notify users of messages in streams they are members of and have subscribed to.
A subscription to a stream includes the transport identifier, and an optional event type filter.

Transports are implemented externally to the API. The api fires an event to a queue with an event and a list of subscriptions. This is important because some transports need to send the events individually, and others can ignore this data and send one alert for all subscribers.

# Notification Replies
If a transport implementation supports it, it can fire a signed event to a return queue that the api watches.
