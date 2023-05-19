<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

/**
 * Upgraders in Dokan Pro was introduced in v3.0.7, but there was
 * a bug for which, the upgrader updates the db version without
 * running the upgrades. So in 3.0.8 we are going to run the upgrade
 * from 3.0.7.
 */
class V_3_0_8 extends V_3_0_7 {}
