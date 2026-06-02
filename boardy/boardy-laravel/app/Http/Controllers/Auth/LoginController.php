protected function authenticated(Request $request, $user)
{
    // Если есть OAuth параметры — перенаправляем обратно на авторизацию
    if ($request->has('client_id')) {
        $params = http_build_query([
            'client_id' => $request->client_id,
            'redirect_uri' => $request->redirect_uri,
            'response_type' => $request->response_type,
            'state' => $request->state,
            'scope' => $request->scope,
        ]);
        return redirect('/oauth/authorize?' . $params);
    }
    
    return redirect('/posts');
}
