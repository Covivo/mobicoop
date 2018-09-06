'use strict';

import faker from 'faker';

class City{
        randomCity = faker.address.city();
  name = `${this.randomCity} ... prés de Covivo`;
}

export default City;