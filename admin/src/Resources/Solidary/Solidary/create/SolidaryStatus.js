import React from 'react';
import { SelectInput } from 'react-admin';

/*
Status of the record : 
0 = asked
1 = refused
2 = pending
3 = looking for solution
4 = follow up
5 = closed
*/

const statusChoices = [
  { id: 0, name: 'asked' },
  { id: 1, name: 'refused' },
  { id: 2, name: 'pending' },
  { id: 3, name: 'looking for solution' },
  { id: 4, name: 'follow up' },
  { id: 5, name: 'closed' },
];

const SolidaryStatus = (props) => (
  <SelectInput
    label="Avancement de la demande"
    source="status"
    choices={statusChoices}
    {...props}
  />
);

export default SolidaryStatus;
