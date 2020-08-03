import React, { useState, useEffect } from 'react';
import { useDataProvider, useNotify } from 'react-admin';
import omit from 'lodash.omit';

import DropDownButton from '../../../components/button/DropDownButton';
import { SolidaryMessagesModal } from '../Solidary/SolidaryMessagesModal';
import { SolidarySMSModal } from '../Solidary/SolidarySMSModal';
import { usernameRenderer } from '../../../utils/renderers';

export const SMS_CONTACT_OPTION = 'SMS_CONTACT_OPTION';
export const MESSAGE_CONTACT_OPTION = 'MESSAGE_CONTACT_OPTION';
export const ADDPOTENTIAL_OPTION = 'ADDPOTENTIAL_OPTION';

const resolveOptions = (solidary) => ({
  [ADDPOTENTIAL_OPTION]: `Ajouter comme conducteur potentiel${
    solidary
      ? ` de ${usernameRenderer({
          record: solidary.solidaryUser.user,
        })}`
      : ''
  }`,
  [MESSAGE_CONTACT_OPTION]: 'Écrire directement vers messagerie',
  [SMS_CONTACT_OPTION]: 'Envoyer directement un SMS',
});

const createSolidarySolutionResolver = (dataProvider) => async (userId, solidary) => {
  // Search an existing solution with this user in solidary

  const matchingSolution = ((solidary && solidary.solutions) || []).find((solution) => {
    return solution.UserId === userId;
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

  // Attempt to find a matching solution and create it

  // @TODO: Will work when authorId is available on the API
  // Moreover, shouldn't we retrieve the corresponding solution matchin instead of checking user ?
  const matching = matchings.find((m) => m.solidaryResultCarpool.authorId === userId);
  if (!matching) {
    throw new Error("Can't find matching solution");
  }

  return dataProvider
    .create('solidary_solutions', {
      data: { solidaryMatching: matching.solidaryMatching.id },
    })
    .then((response) => (response ? response.data.originId : null));
};

const createActionPropsResolver = (dataProvider) => {
  const ensureSolidarySolutionId = createSolidarySolutionResolver(dataProvider);

  return async (action, { userId, solidary }) => {
    if ([MESSAGE_CONTACT_OPTION, SMS_CONTACT_OPTION, ADDPOTENTIAL_OPTION].includes(action)) {
      return ensureSolidarySolutionId(userId, solidary).then((solidarySolutionId) => {
        if (solidarySolutionId) {
          return { solidarySolutionId, solidaryId: solidary.originId };
        }
      });
    }

    return {};
  };
};

const AddSolidaryNotification = () => {
  const notify = useNotify();

  useEffect(() => {
    notify('Bénévole ajouté comme conducteur potentiel', 'success');
  }, []);

  return null;
};

export const SolidaryUserVolunteerActionDropDown = ({
  userId,
  solidary,
  omittedOptions,
  onActionFinished,
}) => {
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
      userId,
    })
      .then((actionProps) => {
        setAction(action);
        setActionProps(actionProps);
        onActionFinished && onActionFinished(action);
      })
      .catch((e) => notify(e.message, 'warning'))
      .finally(() => {
        setLoading(false);
      });
  };

  return (
    <>
      <DropDownButton
        options={omit(resolveOptions(solidary), omittedOptions)}
        onSelect={handleSetAction}
      />
      {!loading && action === ADDPOTENTIAL_OPTION && <AddSolidaryNotification />}
      {!loading && action === MESSAGE_CONTACT_OPTION && (
        <SolidaryMessagesModal {...actionProps} onClose={handleCloseModal} />
      )}
      {!loading && action === SMS_CONTACT_OPTION && (
        <SolidarySMSModal {...actionProps} onClose={handleCloseModal} />
      )}
    </>
  );
};

SolidaryUserVolunteerActionDropDown.defaultProps = {
  omittedOptions: [],
};
