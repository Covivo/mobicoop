import React, { useState } from 'react';
import { useDataProvider, useNotify } from 'react-admin';

import DropDownButton from '../../../components/button/DropDownButton';
import { SolidaryMessagesModal } from '../Solidary/SolidaryMessagesModal';
import { SolidarySMSModal } from '../Solidary/SolidarySMSModal';
import { usernameRenderer } from '../../../utils/renderers';

const SMS_CONTACT_OPTION = 'SMS_CONTACT_OPTION';
const MESSAGE_CONTACT_OPTION = 'MESSAGE_CONTACT_OPTION';
const ADDPOTENTIAL_OPTION = 'ADDPOTENTIAL_OPTION';

const resolveOptions = (solidary) => ({
  [MESSAGE_CONTACT_OPTION]: `Ajouter comme conducteur potentiel de ${usernameRenderer({
    record: solidary.solidaryUser.user,
  })}`,
  [SMS_CONTACT_OPTION]: 'Écrire directement vers messagerie',
  [ADDPOTENTIAL_OPTION]: 'Envoyer directement un SMS',
});

const createSolidarySolutionResolver = (dataProvider) => async (solidaryUserId, solidary) => {
  const matchingSolution = ((solidary && solidary.solutions) || []).find(({ UserId, id }) => {
    return UserId === solidaryUserId;
  });

  if (matchingSolution) {
    return matchingSolution.id;
  }

  const { data: matchings } = await dataProvider.getList('solidary_searches', {
    pagination: { page: 1, perPage: 50 },
    sort: {},
    filter: {
      way: 'outward',
      solidary: `/solidaries/${solidary.originId}`,
      type: 'carpool',
    },
  });

  let matching = matchings.find((m) => m.solidaryResultCarpool.authorId === solidaryUserId);
  if (!matching) {
    return null;
  }

  return dataProvider
    .create('solidary_solutions', {
      data: { solidaryMatching: matching.solidaryMatching.id },
    })
    .then(() => {
      // @TODO: Retrieve the solidaryId here
      return null;
    });
};

const createActionPropsResolver = (dataProvider) => {
  const ensureSolidarySolutionId = createSolidarySolutionResolver(dataProvider);

  return async (action, { solidaryUserId, solidary }) => {
    if ([MESSAGE_CONTACT_OPTION, SMS_CONTACT_OPTION].includes(action)) {
      return ensureSolidarySolutionId(solidaryUserId, solidary).then((solidarySolutionId) => {
        if (solidarySolutionId) {
          return { solidarySolutionId, solidaryId: solidary.originId };
        }

        throw new Error('Une erreur est survenue');
      });
    }

    return {};
  };
};

export const SolidaryUserVolunteerActionDropDown = ({ solidary, record }) => {
  const [action, setAction] = useState(null);
  const [actionProps, setActionProps] = useState({});
  const [loading, setLoading] = useState(false);
  const notify = useNotify();

  const dataProvider = useDataProvider();
  const actionPropsResolver = createActionPropsResolver(dataProvider);

  const handleCloseModal = () => {
    setAction(null);
    setActionProps({});
  };

  const handleSetAction = (_, action) => {
    setAction(null);
    setLoading(true);

    actionPropsResolver(action, {
      solidary,
      solidaryUserId: record.originId,
    })
      .then((a) => {
        setAction(action);
        setActionProps(a);
      })
      .catch((e) => notify('Une erreur est survenue', 'warning'))
      .finally(() => setLoading(false));
  };

  return (
    <>
      <DropDownButton options={resolveOptions(solidary)} onSelect={handleSetAction} />
      {!loading && action === MESSAGE_CONTACT_OPTION && (
        <SolidaryMessagesModal {...actionProps} onClose={handleCloseModal} />
      )}
      {!loading && action === SMS_CONTACT_OPTION && (
        <SolidarySMSModal {...actionProps} onClose={handleCloseModal} />
      )}
    </>
  );
};
