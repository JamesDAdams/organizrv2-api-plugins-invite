<?php
/**
 * @OA\Tag(
 *     name="plugins-invites2",
 *     description="Media Invite Plugin"
 * )
 */
/**
 * @OA\Schema(
 *     schema="getInvites2",
 *     type="object",
 *     @OA\Property(
 *      property="response",
 *      type="object",
 *      @OA\Property(
 *          property="result",
 *          description="success or error",
 *          type="string",
 *          example="success",
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="success or error message",
 *          type="string",
 *          example=null,
 *      ),
 *      @OA\Property(
 *          property="data",
 *          description="data from api",
 *          type="array",
 *          @OA\Items({
 *          @OA\Property(
 *              property="id",
 *              type="number",
 *              example=1,
 *          ),
 *          @OA\Property(
 *              property="code",
 *              type="string",
 *              example="NN9JH9",
 *          ),
 *          @OA\Property(
 *              property="date",
 *              type="string",
 *              example="2018-09-01 02:02:24",
 *          ),
 *          @OA\Property(
 *              property="email",
 *              type="string",
 *              example="causefX@organizr.app",
 *          ),
 *          @OA\Property(
 *              property="username",
 *              type="string",
 *              example="causefx",
 *          ),
 *          @OA\Property(
 *              property="dateused",
 *              type="string",
 *              example="2018-09-01 02:02:24",
 *          ),
 *          @OA\Property(
 *              property="usedby",
 *              type="string",
 *              example="causefx",
 *          ),
 *          @OA\Property(
 *              property="ip",
 *              type="string",
 *              example="10.0.0.0",
 *          ),
 *          @OA\Property(
 *              property="valid",
 *              type="string",
 *              example="No",
 *          ),
 *          @OA\Property(
 *              property="type",
 *              type="string",
 *              example="Plex",
 *          )
 * })
 *      ),
 *  ),
 * )
 */
/**
 * @OA\Schema(
 *     schema="createInviteCode",
 *     type="object",
 *     @OA\Property(
 *      property="response",
 *      type="object",
 *      @OA\Property(
 *          property="result",
 *          description="success or error",
 *          type="string",
 *          example="success",
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="success or error message",
 *          type="string",
 *          example="Invite Code: XYXYXY has been created",
 *      ),
 *      @OA\Property(
 *          property="data",
 *          description="data from api",
 *          type="string",
 *          example=null,
 *      ),
 *  ),
 * )
 */
/**
 * @OA\Schema(
 *     schema="verifyInviteCode",
 *     type="object",
 *     @OA\Property(
 *      property="response",
 *      type="object",
 *      @OA\Property(
 *          property="result",
 *          description="success or error",
 *          type="string",
 *          example="success",
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="success or error message",
 *          type="string",
 *          example="Code has been verified",
 *      ),
 *      @OA\Property(
 *          property="data",
 *          description="data from api",
 *          type="string",
 *          example=null,
 *      ),
 *  ),
 * )
 */
/**
 * @OA\Schema(
 *     schema="useInviteCode",
 *     type="object",
 *     @OA\Property(
 *      property="response",
 *      type="object",
 *      @OA\Property(
 *          property="result",
 *          description="success or error",
 *          type="string",
 *          example="success",
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="success or error message",
 *          type="string",
 *          example="Plex/Emby User now has access to system",
 *      ),
 *      @OA\Property(
 *          property="data",
 *          description="data from api",
 *          type="string",
 *          example=null,
 *      ),
 *  ),
 * )
 */
/**
 * @OA\Schema(
 *     schema="deleteInviteCode",
 *     type="object",
 *     @OA\Property(
 *      property="response",
 *      type="object",
 *      @OA\Property(
 *          property="result",
 *          description="success or error",
 *          type="string",
 *          example="success",
 *      ),
 *      @OA\Property(
 *          property="message",
 *          description="success or error message",
 *          type="string",
 *          example="Code has been deleted",
 *      ),
 *      @OA\Property(
 *          property="data",
 *          description="data from api",
 *          type="string",
 *          example=null,
 *      ),
 *  ),
 * )
 */
$app->get('/plugins/invites2/settings', function ($request, $response, $args) {
	/**
	 * @OA\Get(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2/settings",
	 *     summary="Get settings",
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/pluginSettingsPage"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized"),
	 *     security={{ "api_key":{} }}
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest(1, true)) {
			$GLOBALS['api']['response']['data'] = $Invites2->_invites2PluginGetSettings();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/invites2', function ($request, $response, $args) {
	/**
	 * @OA\Get(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2",
	 *     summary="Get All Invites2",
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/getInvites2"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized"),
	 *     security={{ "api_key":{} }}
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest($Invites2->config['INVITES2-Auth-include'], true)) {
			$GLOBALS['api']['response']['data'] = $Invites2->_invites2PluginGetCodes();
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->post('/plugins/invites2', function ($request, $response, $args) {
	/**
	 * @OA\Post(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2",
	 *     summary="Create Invite Code",
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/createInviteCode"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized"),
	 *     security={{ "api_key":{} }}
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest($Invites2->config['INVITES2-Auth-include'], true)) {
			$Invites2->_invites2PluginCreateCode($Invites2->apiData($request));
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->get('/plugins/invites2/{code}', function ($request, $response, $args) {
	/**
	 * @OA\Get(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2/{code}",
	 *     summary="Verify Invite Code",
	 *     @OA\Parameter(
	 *      name="code",
	 *      description="The Invite Code",
	 *      @OA\Schema(
	 *          type="integer",
	 *          format="int64",
	 *      ),
	 *      in="path",
	 *      required=true
	 *      ),
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/verifyInviteCode"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized")
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest(999, true)) {
			$Invites2->_invites2PluginVerifyCode($args['code']);
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->post('/plugins/invites2/{code}', function ($request, $response, $args) {
	/**
	 * @OA\Post(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2/{code}",
	 *     summary="Use Invite Code",
	 *     @OA\Parameter(
	 *      name="code",
	 *      description="The Invite Code",
	 *      @OA\Schema(
	 *          type="integer",
	 *          format="int64",
	 *      ),
	 *      in="path",
	 *      required=true
	 *      ),
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/useInviteCode"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized")
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest(999, true)) {
			$Invites2->_invites2PluginUseCode($args['code'], $Invites2->apiData($request));
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});
$app->delete('/plugins/invites2/{code}', function ($request, $response, $args) {
	/**
	 * @OA\Delete(
	 *     tags={"plugins-invites2"},
	 *     path="/api/v2/plugins/invites2/{code}",
	 *     summary="Delete Invite Code",
	 *     @OA\Parameter(
	 *      name="code",
	 *      description="The Invite Code",
	 *      @OA\Schema(
	 *          type="integer",
	 *          format="int64",
	 *      ),
	 *      in="path",
	 *      required=true
	 *      ),
	 *     @OA\Response(
	 *      response="200",
	 *      description="Success",
	 *      @OA\JsonContent(ref="#/components/schemas/deleteInviteCode"),
	 *     ),
	 *     @OA\Response(response="401",description="Unauthorized"),
	 *     security={{ "api_key":{} }}
	 * )
	 */
	$Invites2 = new Invites2();
	if ($Invites2->checkRoute($request)) {
		if ($Invites2->qualifyRequest($Invites2->config['INVITES2-Auth-include'], true)) {
			$Invites2->_invites2PluginDeleteCode($args['code']);
		}
	}
	$response->getBody()->write(jsonE($GLOBALS['api']));
	return $response
		->withHeader('Content-Type', 'application/json;charset=UTF-8')
		->withStatus($GLOBALS['responseCode']);
});