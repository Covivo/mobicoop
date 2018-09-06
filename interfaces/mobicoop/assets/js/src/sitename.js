'use strict';

import City from './Citygenerator';

export default function (siteName) {
  siteName = siteName.toUpperCase();
  let randomCity = new City().name;
  return ` ${siteName}(the best route app), you can go to ${randomCity}`;
}