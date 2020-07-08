import React from 'react';
import get from 'lodash.get';

import { Mutation, Button } from 'react-admin';

export const CreateSolidarySolutionButton = ({ record, source }) => {
  const solidaryMatching = get(record, source);
  console.log({ solidaryMatching, record });
  return (
    <Mutation
      type="create"
      resource="solidary_solutions"
      payload={{ data: { solidaryMatching } }}
      options={{
        onSuccess: {
          notification: { body: 'Trajet ajouté à la demande !' },
        },
        onFailure: {
          notification: {
            body: 'Erreur : le trajet ne peut pas être ajouté à la demande.',
            level: 'warning',
          },
        },
      }}
    >
      {(approve, { loading }) => (
        <Button label="Sélectionner" onClick={approve} disabled={loading} />
      )}
    </Mutation>
  );
};
