import React from 'react';
import { parse } from 'query-string';
import { Create, SimpleForm } from 'react-admin';

import GeocompleteInput from '../../Component/Utilities/geocomplete';

export const AddressesCreate = (props) => {
  const { user_id: user_id_string } = parse(props.location.search);
  const user_id = user_id_string ? parseInt(user_id_string, 10) : '';
  const redirect = user_id ? `/users/${user_id}/show/comments` : 'show';

  return (
    <Create {...props}>
      <SimpleForm defaultValue={user_id} redirect={redirect}>
        <GeocompleteInput />
      </SimpleForm>
    </Create>
  );
};
