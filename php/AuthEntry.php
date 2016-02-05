<?php namespace MFF\ppanel;

class AuthEntry {
  public $srv, $nick, $pwdHash, $privs, $lastLogin;
  public function __construct($srv, $nick, $pwdHash, $privs, $lastLogin) {
    $this->srv = $srv;
    $this->nick = $nick;
    $this->pwdHash = $pwdHash;
    $this->privs = $privs;
    $this->lastLogin = $lastLogin;
  }

  ///
  /// @brief Cheks if given nickname and password matches the AuthEntry
  /// @param $nick   Player's nickname
  /// @param $passwd Password
  ///
  public function checkAuth($nick, $passwd) {
    if (substr($this->pwdHash, 0, 3) === '#1#') {
      // 1st case: new, SHA256 SRP logins
      // https://tools.ietf.org/html/rfc2945#section-3
      $pwdArr = explode('#', $this->pwdHash);
      $x = gmp_import(hash("sha256", base64_decode($pwdArr[2]) . hash("sha256", strtolower($nick) . ':' . $passwd, true), true), 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
      $N = gmp_init(
        "AC6BDB41324A9A9BF166DE5E1389582FAF72B6651987EE07FC3192943DB56050A37329CBB4" .
        "A099ED8193E0757767A13DD52312AB4B03310DCD7F48A9DA04FD50E8083969EDB767B0CF60" .
        "95179A163AB3661A05FBD5FAAAE82918A9962F0B93B855F97993EC975EEAA80D740ADBF4FF" .
        "747359D041D5C33EA71D281E446B14773BCA97B43A23FB801676BD207A436C6481F1D2B907" .
        "8717461A5B9D32E688F87748544523B524B0D57D5EA77A2775D2ECFA032CFBDBF52FB37861" .
        "60279004E57AE6AF874E7303CE53299CCC041C7BC308D82A5698F3A8D0C38271AE35F8E9DB" .
        "FBB694B5C803D89F7AE435DE236D525F54759B65E372FCD68EF20FA7111F9E4AFF73", 16);
      $g = gmp_init(2);
      $v = gmp_powm($g, $x, $N);
      // strpos to check if the saved has is the same as our hash, minus the possible base64 padding
      return strpos(base64_encode(gmp_export($v, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN)), $pwdArr[3]) === 0;
    } else {
      // 2nd case: old, pre-SRP logins using salted SHA1
      // src/util/auth.cpp#34 at rev 0bf1984d2c9fb3a9dc73303551c18906c3c9482b
      // https://github.com/minetest/minetest/blob/0bf1984d2c9fb3a9dc73303551c18906c3c9482b/src/util/auth.cpp#L34
      return strpos(base64_encode(hash("sha1", $nick . $passwd, true)), $this->pwdHash) === 0;
    }
    return false;
  }
}

?>
