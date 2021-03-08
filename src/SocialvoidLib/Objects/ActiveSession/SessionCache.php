<?php

    /** @noinspection PhpUnused */
    /** @noinspection PhpMissingFieldTypeInspection */

    namespace SocialvoidLib\Objects\ActiveSession;

    use SocialvoidLib\Exceptions\Standard\Network\InvalidPeerInputException;
    use SocialvoidLib\Objects\ActiveSession\SessionCache\PeerResolveCache;
    use SocialvoidLib\Objects\User;

    /**
     * Class SessionCache
     * @package SocialvoidLib\Objects\ActiveSession
     */
    class SessionCache
    {
        /**
         * Array of partially resolved peers for cache purposes
         *
         * @var PeerResolveCache[]
         */
        public $PeerResolveCache;

        /**
         * SessionCache constructor.
         */
        public function __construct()
        {
            $this->PeerResolveCache = [];
        }

        /**
         * Caches a peer partially into the session cache and deletes the oldest
         * cache in the session cache.
         *
         * @param User $user
         * @throws InvalidPeerInputException
         */
        public function cachePeer(User $user)
        {
            // If it's already cached, leave it.
            $CurrentCache = $this->getCachedPeer($user->PublicID);
            if($CurrentCache !== null)
            {
                // The only time it should update the cache is if the username changed.
                if($user->UsernameSafe == $CurrentCache->PeerUsernameSafe)
                {
                    return;
                }
            }

            $MaxResolveCount = 20;
            if(defined("SOCIALVOID_LIB_MAX_PEER_RESOLVE_CACHE_COUNT"))
                $MaxResolveCount = SOCIALVOID_LIB_MAX_PEER_RESOLVE_CACHE_COUNT;

            // Delete the oldest peer cache
            $CurrentCount = 0;
            $OldestPeerIndex = null;
            $OldestPeerTime = (int)time();
            if(count($this->PeerResolveCache) >= $MaxResolveCount)
            {
                foreach($this->PeerResolveCache as $resolveCache)
                {
                    if($resolveCache->LastUpdatedTimestamp < $OldestPeerTime)
                        $OldestPeerIndex = $CurrentCount;

                    $CurrentCount += 1;
                }

                // Unset and reconstruct
                unset($this->PeerResolveCache[$OldestPeerIndex]);
                $Reconstruct = [];
                foreach($this->PeerResolveCache as $resolveCache)
                    $Reconstruct[] = $resolveCache;
                $this->PeerResolveCache = $Reconstruct;
            }
        }

        /**
         * Gets the current cached peer
         *
         * @param $peer
         * @return PeerResolveCache|null
         * @throws InvalidPeerInputException
         */
        public function getCachedPeer($peer): ?PeerResolveCache
        {
            // Probably an ID
            if(ctype_digit($peer))
            {
                foreach($this->PeerResolveCache as $resolveCache)
                {
                    if($resolveCache->PeerID == $peer)
                        return $resolveCache;
                }
            }
            // It's a username
            elseif(substr($peer, 0, 1) == "@")
            {
                foreach($this->PeerResolveCache as $resolveCache)
                {
                    if($resolveCache->PeerUsernameSafe == strtolower(substr($peer, 1)))
                        return $resolveCache;
                }
            }
            // It's a public ID
            elseif(strlen($peer) == 64)
            {
                foreach($this->PeerResolveCache as $resolveCache)
                {
                    if($resolveCache->PeerPublicID == $peer)
                        return $resolveCache;
                }
            }
            else
            {
                throw new InvalidPeerInputException("The given peer input is invalid", $peer);
            }

            return null;
        }

        /**
         * Returns the array representation of the PeerResolveCache
         *
         * @return array
         */
        public function peerResolveCacheToArray(): array
        {
            $results = [];

            foreach($this->PeerResolveCache as $value)
                $results[] = $value->toArray();

            return $results;
        }

        /**
         * Returns an array representation of this object
         *
         * @return array
         */
        public function toArray(): array
        {
            return [
                "peer_resolve_cache" => $this->peerResolveCacheToArray()
            ];
        }

        /**
         * Constructs the object from an array representation
         *
         * @param array $data
         * @return SessionCache
         */
        public static function fromArray(array $data): SessionCache
        {
            $SessionCacheObject = new SessionCache();

            if(isset($data["peer_resolve_cache"]))
            {
                $SessionCacheObject->PeerResolveCache = [];
                foreach($data["peer_resolve_cache"] as $datum)
                    $SessionCacheObject->PeerResolveCache[] = PeerResolveCache::fromArray($datum);
            }

            return $SessionCacheObject;
        }
    }