### v1.0.0.1 Alpha
 - Bumped version to 1.0.0.1 in `net.intellivoid.socialvoidlib`
 - Bumped version to 1.0.0.1 in `net.intellivoid.socialvoid_rpc`
 - Bumped version to 1.0.0.1 in `net.intellivoid.socialvoid_admin`
 - Added method timeline.repost_post
 - Added alternative method for an RPC Server (No BackgroundWorker) which uses less resources
 - Post IDs now use UUID v1
 - CDN Server now uses HttpStream to stream content efficiently without memory overhead
 - Added internal method for recursively resolving post entities
 - Removed object DisplayPictureSizes[] from Peer (standard) to reduce response size
 - Added the ability to delete posts
 - Updated deleted post structure
 - Rewrote posts related tables such as `posts.sql`, `posts_likes.sql`, `posts_quotes.sql`, 
   `posts_replies.sql`, `posts_reposts.sql` to work with slave servers
 - Added RPC method timeline.compose
 - Added RPC method timeline.delete
 - Added RPC method timeline.get_likes
 - Added RPC method timeline.get_post
 - Added RPC method timeline.get_quotes
 - Added RPC method timeline.get_replies
 - Added RPC method timeline.get_reposted_peers
 - Added RPC method timeline.like
 - Added RPC method timeline.quote
 - Added RPC method timeline.reply
 - Added RPC method timeline.repost
 - Added RPC method timeline.retrieve_feed
 - Added RPC method timeline.unlike
 - Added RPC method account.clear_profile_biography
 - Added RPC method account.clear_profile_location
 - Added RPC method account.clear_profile_url
 - Added RPC method account.update_profile_biography
 - Added RPC method account.update_location
 - Added RPC method account.change_profile_name
 - Added RPC method account.update_profile_url
 - Added RPC method network.get_followers
 - Added RPC method network.get_following
 - Added RPC method network.get_profile
 - Added RPC method network.follow_peer
 - Added RPC method network.unfollow_peer
 - Added a version information menu to `net.intellivoid.socialvoid_admin`
 - Fixed client hash validation bug
 - Fixed worker monitoring logic to use BackgroundWorker's builtin monitor
 - Added various debugging tools to `net.intellivoid.socialvoid_admin`
 - Corrected various casting expressions to enforce the correct return types
 - Added missing methods to LikesRecordManager to get records and counts
 - Refactored Reposts, Quotes, Replies and Likes managers to use slave tables and be more efficient in data storage
 - Rewrote FollowersData and FollowersState to be Peer Relations, this cannot be scaled and must be stored on the master
   database, but the methods has been rewritten to be more straightforward and allow for "Blocking" features (Not yet implemented)
 - Rewrote peer relations to be more streamlined and less wasteful on data storage
 - Added standard error Network.BlockedByPeer
 - Added standard error Network.BlockedPeer
 - Added standard error Network.SelfInteractionNotPermitted
 - Added standard error Validation.InvalidLimitValue
 - Added standard error Validation.InvalidOffsetValue
 - Added Authentication checks to various methods including the CDN server
 - Added standard object Profile
 - Added properties retrieve_likes_max_limit, retrieve_reposts_max_limit, retrieve_replies_max_limit,
   retrieve_quotes_max_limit, retrieve_followers_max_limit, retrieve_following_max_limit to Standard object
   ServerInformation
 - Various performance improvements and bug fixes

### v1.0.0.2 Alpha